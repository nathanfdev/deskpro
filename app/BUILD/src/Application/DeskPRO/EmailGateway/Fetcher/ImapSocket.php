<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
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
    private $message_uids;

    /**
     * Mailbox name to move messages after processing.
     *
     * @var string Mailbox name
     */
    private $archive_mailbox;

    /**
     * Mailbox name to read messages from.
     *
     * @var string Mailbox name
     */
    private $read_mailbox;

    /**
     * @var Protocol\Imap
     */
    protected $protocol;

    /**
     * @throws \Exception
     *
     * @return Storage\Imap
     */
    protected function _initConnection()
    {
        $options = [];

        $incoming_account = EmailAccountUtil::decryptIncomingAccount($this->account->incoming_account, App::$container->get('dp_enc'));

        switch ($incoming_account->getType()) {
            case 'gmail':
                /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig $gmail_config */
                $gmail_config = $incoming_account;

                $options['host'] = 'imap.gmail.com';
                $options['port'] = 993;
                $options['user'] = $gmail_config->user;
                $options['mode'] = $gmail_config->mode ?: self::MODE_DELETE; // delete in gmail just means archive

                $this->logger->log('Trying to refresh gmail access token', 'debug');
                $client = new \Google_Client();
                $client->setClientId($gmail_config->clientId);
                $client->setClientSecret($gmail_config->clientSecret);
                $client->setScopes(\Google_Service_Gmail::MAIL_GOOGLE_COM);
                $client->setAccessToken($gmail_config->token);
                $client->setAccessType('offline');
                $client->refreshToken($gmail_config->refreshToken);
                $data = $client->getAccessToken();
                if (!empty($data['access_token'])) {
                    $options['token'] = $data['access_token'];
                }

                break;

            default:
                throw new \InvalidArgumentException('Unknown account type: '.$incoming_account->getType());
        }

        $this->mode            = $options['mode'];
        $this->archive_mailbox = !empty($options['archive_mailbox']) ? $options['archive_mailbox'] : 'DP_Archive';
        $this->read_mailbox    = !empty($options['read_mailbox']) ? $options['read_mailbox'] : null;

        $this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

        $options['logger'] = $this->logger;

        $this->protocol = new Protocol\Imap($options['host'], $options['port'], 'ssl');
        $this->oauth2Authenticate($options['user'], $options['token']);
        $this->storage = new Storage\Imap($this->protocol);

        if ($this->archive_mailbox === $this->storage->getCurrentFolder()) {
            throw new \Exception('The current mailbox is reserved for processed emails, it can not be used as the primary mailbox');
        }

        if ($this->mode === self::MODE_ARCHIVE) {
            try {
                $this->storage->selectFolder($this->archive_mailbox);
            } catch (Storage\Exception\RuntimeException $e) {
                $this->storage->createFolder($this->archive_mailbox);
            }
        }

        if ($this->read_mailbox) {
            try {
                $this->storage->selectFolder($this->read_mailbox);
            } catch (Storage\Exception\RuntimeException $e) {
                $this->storage->createFolder($this->read_mailbox);
            }
        }

        if ($this->mode == self::MODE_READ) {
            $this->message_uids = $this->protocol->search([Storage::FLAG_UNSEEN]) ?: [];
        } else {
            $this->message_uids = $this->protocol->search(['ALL']) ?: [];
        }

        $this->logger->log('Read IDs: '.implode(', ', $this->message_uids), 'debug');

        return $this->storage;
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

        return array_shift($this->message_uids);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    public function _readNext()
    {
        $message_uid = $this->getNextMessageUid();

        if ($message_uid === null) {
            return;
        }

        $raw_message       = new RawMessage();
        $raw_message->id   = $message_uid;
        $raw_message->uid  = $message_uid;
        $raw_message->size = $this->storage->getSize($message_uid) ?: 0;

        $this->logger->log(sprintf('Message UID: %s', $raw_message->uid), 'debug');
        $this->logger->log(sprintf('Message size: %s bytes', $raw_message->size), 'debug');

        if ($this->max_size && $raw_message->size && $raw_message->size > $this->max_size) {
            // If we are here, it means that message is larger than the max size
            // So, we won't store the whole message, only the headers.
            $raw_message->content = $this->storage->getRawHeader($message_uid)."\n\n";
            $this->logger->log('Message too big, only fetching headers', 'debug');
        } else {
            // Otherwise store the whole message
            $raw_message->content = $this->storage->getRawContent($message_uid);
        }

        $headers = null;

        $EOL = "\n";
        if (strpos($raw_message->content, $EOL.$EOL)) {
            list($headers) = explode($EOL.$EOL, $raw_message->content, 2);
        } elseif ($EOL != "\r\n" && strpos($raw_message->content, "\r\n\r\n")) {
            list($headers) = explode("\r\n\r\n", $raw_message->content, 2);
        } elseif ($EOL != "\n" && strpos($raw_message->content, "\n\n")) {
            list($headers) = explode("\n\n", $raw_message->content, 2);
        } else {
            @list($headers) = @preg_split("%([\r\n]+)\\1%U", $raw_message->content, 2);
        }

        $raw_message->headers = $headers;

        return $raw_message;
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
                $this->logger->log("Marked $id as seen", 'debug');
                break;

            case self::MODE_ARCHIVE:
                $this->storage->moveMessage($id, $this->archive_mailbox);
                $this->logger->log("Moved $id to {$this->archive_mailbox}", 'debug');
                break;

            case self::MODE_DELETE:
                $this->storage->removeMessage($id);
                $this->logger->log("Deleted $id", 'debug');
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
    protected function oauth2Authenticate($email, $accessToken)
    {
        $authenticateParams = ['XOAUTH2', $this->constructAuthString($email, $accessToken)];
        $this->protocol->sendRequest('AUTHENTICATE', $authenticateParams);
        while (true) {
            $response = '';
            $is_plus  = $this->protocol->readLine($response, '+', true);
            if ($is_plus) {
                // error_log("got an extra server challenge: $response");
                // Send empty client response.
                $this->protocol->sendRequest('');
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
