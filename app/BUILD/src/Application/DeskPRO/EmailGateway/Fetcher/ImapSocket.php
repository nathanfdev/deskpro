<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\NewSettings\SettingsBag;
use Zend\Mail\Protocol;
use Zend\Mail\Storage;

/**
 * Fetches mail from a imap server.
 */
class ImapSocket extends AbstractFetcher
{
    /**
     * Just marks messages as read once they are processed.
     */
    const MODE_READ = 'read';

    /**
     * Deletes messages once they are processed.
     */
    const MODE_DELETE = 'delete';

    /**
     * Archive messages (moves to a folder) once they are processed.
     */
    const MODE_ARCHIVE = 'archive';

    /**
     * @var int
     */
    private $mode = self::MODE_READ;

    /**
     * The IMAP Storage.
     *
     * @var Storage\Imap
     */
    protected $storage;

    /**
     * Messages retrieved in the current fetch.
     *
     * @var array An array of message ids
     */
    private $messageUids;

    /**
     * Mailbox name to move messages after processing.
     *
     * @var string Mailbox name
     */
    private $archiveMailbox;

    /**
     * Mailbox name to read messages from.
     *
     * @var string Mailbox name
     */
    private $readMailbox;

    /**
     * @throws \Exception
     *
     * @return Storage\Imap
     */
    protected function _initConnection()
    {
        $incomingAccount = EmailAccountUtil::decryptIncomingAccount($this->account->incoming_account, App::$container->get('dp_enc'));

        switch ($incomingAccount->getType()) {
            case 'gmail':
                $this->addToSessionLog('Trying to refresh gmail access token', 'debug');
                $options = self::initOptions(
                    $incomingAccount,
                    App::$container->get('settings_resolver')->getGlobalSettings()
                );
                break;

            default:
                throw new \InvalidArgumentException('Unknown account type: '.$incomingAccount->getType());
        }

        $this->mode           = $options['mode'];
        $this->archiveMailbox = !empty($options['archive_mailbox']) ? $options['archive_mailbox'] : 'DP_Archive';
        $this->readMailbox    = !empty($options['read_mailbox']) ? $options['read_mailbox'] : null;

        $this->addToSessionLog("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

        $options['logger'] = $this->logger;

        $protocol = new Protocol\Imap($options['host'], $options['port'], 'ssl');
        self::oauth2Authenticate($options['user'], $options['token'], $protocol);
        $this->storage = new Storage\Imap($protocol);

        if ($this->archiveMailbox === $this->storage->getCurrentFolder()) {
            throw new \Exception('The current mailbox is reserved for processed emails, it can not be used as the primary mailbox');
        }

        if ($this->mode === self::MODE_ARCHIVE) {
            try {
                $this->storage->selectFolder($this->archiveMailbox);
            } catch (Storage\Exception\RuntimeException $e) {
                $this->storage->createFolder($this->archiveMailbox);
            }
        }

        if ($this->readMailbox) {
            try {
                $this->storage->selectFolder($this->readMailbox);
            } catch (Storage\Exception\RuntimeException $e) {
                $this->storage->createFolder($this->readMailbox);
            }
        }

        if ($this->mode == self::MODE_READ) {
            $this->messageUids = $this->protocol->search([Storage::FLAG_UNSEEN]) ?: [];
        } else {
            $this->messageUids = $this->protocol->search(['ALL']) ?: [];
        }

        $this->addToSessionLog('Read IDs: '.implode(', ', $this->messageUids), 'debug');

        return $this->storage;
    }

    public static function initOptions(GmailConfig $config, SettingsBag $settings)
    {
        $options         = [];
        $options['host'] = 'imap.gmail.com';
        $options['port'] = 993;
        $options['user'] = $config->user;
        $options['mode'] = $config->mode ?: self::MODE_DELETE; // delete in gmail just means archive

        $client = new \Google_Client();
        $client->setClientId($settings->get('core_email.google_oauth_client_id'));
        $client->setClientSecret($settings->get('core_email.google_oauth_secret'));
        $client->setScopes([\Google_Service_Gmail::MAIL_GOOGLE_COM]);
        $client->setAccessToken($config->token);
        $client->setAccessType('offline');
        $client->refreshToken($config->refreshToken);

        $data = $client->getAccessToken();
        if (!empty($data['access_token'])) {
            $options['token'] = $data['access_token'];
        }

        return $options;
    }

    /**
     * Gets the next message
     * Iterates over the fetched IDs and retrieves the next message in list.
     *
     * @return int
     */
    private function getNextMessageUid()
    {
        $this->getStorage();

        return array_shift($this->messageUids);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    public function _readNext()
    {
        $messageUid = $this->getNextMessageUid();

        if ($messageUid === null) {
            return null;
        }

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $messageUid;
        $rawMessage->uid  = $messageUid;
        $rawMessage->size = $this->storage->getSize($messageUid) ?: 0;

        $this->addToSessionLog(sprintf('Message UID: %s', $rawMessage->uid), 'debug');
        $this->addToSessionLog(sprintf('Message size: %s bytes', $rawMessage->size), 'debug');

        if ($this->maxSize && $rawMessage->size && $rawMessage->size > $this->maxSize) {
            // If we are here, it means that message is larger than the max size
            // So, we won't store the whole message, only the headers.
            $rawMessage->content = $this->storage->getRawHeader($messageUid)."\n\n";
            $this->addToSessionLog('Message too big, only fetching headers', 'debug');
        } else {
            // Otherwise store the whole message
            $rawMessage->content = $this->storage->getRawContent($messageUid);
        }

        $headers = null;

        $EOL = "\n";
        if (strpos($rawMessage->content, $EOL.$EOL)) {
            list($headers) = explode($EOL.$EOL, $rawMessage->content, 2);
        } elseif ($EOL != "\r\n" && strpos($rawMessage->content, "\r\n\r\n")) {
            list($headers) = explode("\r\n\r\n", $rawMessage->content, 2);
        } elseif ($EOL != "\n" && strpos($rawMessage->content, "\n\n")) {
            list($headers) = explode("\n\n", $rawMessage->content, 2);
        } else {
            @list($headers) = @preg_split("%([\r\n]+)\\1%U", $rawMessage->content, 2);
        }

        $rawMessage->headers = $headers;

        return $rawMessage;
    }

    /**
     * Processes the message after reading it.
     * Moves it to the DP_Mailbox folder marking it "read".
     *
     * @param int $id ID of the message
     */
    public function _doneRead($id)
    {
        switch ($this->mode) {
            case self::MODE_READ:
                // No need to mark message as read, its marked as read automatically by fetching the body
                //$message->setFlag('seen', 1);
                $this->addToSessionLog("Marked $id as seen", 'debug');
                break;

            case self::MODE_ARCHIVE:
                $this->storage->moveMessage($id, $this->archiveMailbox);
                $this->addToSessionLog("Moved $id to {$this->archiveMailbox}", 'debug');
                break;

            case self::MODE_DELETE:
                $this->storage->removeMessage($id);
                $this->addToSessionLog("Deleted $id", 'debug');
                break;

            default:
                throw new \InvalidArgumentException('Unvalid mode: '.$this->mode);
        }
    }

    /**
     * @param $email
     * @param $accessToken
     *
     * @return string
     */
    protected function constructAuthString($email, $accessToken)
    {
        return base64_encode("user=$email\1auth=Bearer $accessToken\1\1");
    }

    /**
     * @param $email
     * @param $accessToken
     *
     * @return bool
     */
    public static function oauth2Authenticate($email, $accessToken, Protocol\Imap $protocol)
    {
        $authenticateParams = ['XOAUTH2', self::constructAuthString($email, $accessToken)];
        $protocol->sendRequest('AUTHENTICATE', $authenticateParams);
        while (true) {
            $response = '';
            $isPlus   = $this->protocol->readLine($response, '+', true);
            if ($isPlus) {
                // error_log("got an extra server challenge: $response");
                // Send empty client response.
                $protocol->sendRequest('');
                continue;
            }

            if (preg_match('/^OK /i', $response)) {
                return true;
            }

            if (preg_match('/^NO /i', $response) ||
                preg_match('/^BAD /i', $response)) {
                throw new Protocol\Exception\RuntimeException('Can\'t authenticate via OAuth. '.$response);
            }

            // Some untagged response, such as CAPABILITY
        }
    }
}
