<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365Config;
use Application\DeskPRO\EmailGateway\Storage;

/**
 * Fetches mail from a imap server.
 */
class Imap extends AbstractFetcher
{
    use NeedsIncomingAccountDecryptionTrait;

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
     * @var \Application\DeskPRO\EmailGateway\Storage\Imap
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
     * Init connection
     *
     * @return Storage\Imap|\Zend\Mail\Storage\AbstractStorage
     *
     * @throws \CannotPerformOperationException
     * @throws \InvalidArgumentException
     * @throws \InvalidCiphertextException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     */
    protected function _initConnection()
    {
        // stubs
        $properties = ['user', 'password'];
        $options = [];

        // decrypt account config
        $incomingAccount = $this->decryptIncomingAccount();

        // setup connection options
        switch ($incomingAccount->getType()) {
            case 'imap':
                /** @var ImapConfig $protocolConfig */
                $protocolConfig = $incomingAccount;

                foreach (array_merge($properties, ['host', 'port', 'mode', 'read_mailbox']) as $property) {
                    if (property_exists($protocolConfig, $property)) {
                        $options[$property] = $protocolConfig->{$property};
                    }
                }

                if ($protocolConfig->secure_mode) {
                    $options['secure']        = $protocolConfig->secure_mode;
                    $options['no_validation'] = $protocolConfig->no_validation;
                }

                if ($protocolConfig->mode === self::MODE_ARCHIVE) {
                    $options['archive_mailbox'] = $protocolConfig->archive_mailbox;
                }

                break;

            case 'gmail':
                /** @var GmailConfig $protocolConfig */
                $protocolConfig = $incomingAccount;

                foreach ($properties as $property) {
                    if (property_exists($protocolConfig, $property)) {
                        $options[$property] = $protocolConfig->{$property};
                    }
                }

                $options = array_merge(
                    $options,
                    [
                        'host'   => 'imap.gmail.com',
                        'port'   => 993,
                        'secure' => 'ssl',
                        'mode'   => self::MODE_DELETE,
                    ]
                );

                break;
            case 'office365':
                /** @var Office365Config $protocolConfig */
                $protocolConfig = $incomingAccount;

                foreach ($properties as $property) {
                    if (property_exists($protocolConfig, $property)) {
                        $options[$property] = $protocolConfig->{$property};
                    }
                }

                $options = array_merge(
                    $options,
                    [
                        'host'   => 'outlook.office365.com',
                        'port'   => 993,
                        'secure' => 'ssl',
                        'mode'   => self::MODE_DELETE,
                    ]
                );

                break;
            default:
                throw new \InvalidArgumentException(
                    "Unknown account type: {$incomingAccount->getType()}"
                );
                break;
        }

        // set mode
        $this->mode = $options['mode'];

        // set archive mailbox
        $this->archiveMailbox =
            (isset($options['archive_mailbox']) && ! is_null($options['archive_mailbox']))
                ? $options['archive_mailbox']
                : 'DP_Archive';

        // set read mailbox
        $this->readMailbox =
            (isset($options['read_mailbox']) && ! is_null($options['read_mailbox']))
                ? $options['read_mailbox']
                : null;

        // pass logger to the storage
        $options['logger'] = $this->logger;

        try {
            // log attempt
            $this->logger->log(
                "Connecting to {$options['user']}@{$options['host']}:{$options['port']}",
                'debug'
            );

            // attempt to connect
            $this->storage = new Storage\Imap($options);

            // log success
            $this->logger->log(
                "Connected to {$options['user']}@{$options['host']}:{$options['port']}",
                'debug'
            );
        } catch (\Exception $exception) {
            // log failure
            $this->logger->log(
                "An error has occured while setting up connection: {$exception->getMessage()}",
                'error'
            );

            throw $exception;
        }

        // check if storage mailbox is not the same as archive mailbox
        if ($this->archiveMailbox === $this->storage->getMailbox()) {
            $exception = new \Exception(
                'The current mailbox is reserved for processed emails, it can not be used as the primary mailbox'
            );

            $this->logger->log(
                "An error has occured while setting up connection: {$exception->getMessage()}",
                'error'
            );

            throw $exception;
        }

        // ensure archive mailbox exists if mode is archive
        if ($this->mode === self::MODE_ARCHIVE) {
            $this->storage->ensureMailboxExists($this->archiveMailbox);
        }

        // ensure read mailbox exists if provided
        if (! is_null($this->readMailbox)) {
            $this->storage->ensureMailboxExists($this->readMailbox);
            $this->storage->setMailBox($this->readMailbox);
        }

        try {
            // attempt to read messages uids
            $this->messageUids = ($this->mode === self::MODE_READ)
                ? $this->storage->getAllUnseenMessageUids()
                : $this->storage->getAllMessageUids();

            // log success
            $this->logger->log(
                'Read IDs: '.implode(', ', $this->messageUids),
                'debug'
            );
        } catch (\Exception $exception) {
            // log failure
            $this->logger->log(
                "Failed to read messages: {$exception->getMessage()}",
                'debug'
            );

            throw $exception;
        }

        // return storage
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

        return array_shift($this->messageUids);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     * @throws \Exception
     */
    public function _readNext()
    {
        if ($this->storage) {
            $this->storage->clearCaches();
        }

        $messageUid = $this->getNextMessageUid();

        if ($messageUid === null) {
            return null;
        }

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $messageUid;
        $rawMessage->uid  = $messageUid;
        $rawMessage->size = $this->storage->getMessageSize($messageUid) ?: 0;

        $this->logger->log(sprintf('Message UID: %s', $rawMessage->uid), 'debug');
        $this->logger->log(sprintf('Message size: %s bytes', $rawMessage->size), 'debug');

        if ($this->maxSize && $rawMessage->size && $rawMessage->size > $this->maxSize) {
            // If we are here, it means that message is larger than the max size
            // So, we won't store the whole message, only the headers.
            $rawMessage->content = $this->storage->getRawHeaders($messageUid)."\n\n";
            $this->logger->log('Message too big, only fetching headers', 'debug');
        } else {
            // Otherwise store the whole message
            $rawMessage->content = $this->storage->getRawMessage($messageUid);
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
     * @throws \InvalidArgumentException
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
                $this->storage->moveMessageMailbox($id, $this->archiveMailbox);
                $this->logger->log("Moved $id to {$this->archiveMailbox}", 'debug');
                break;

            case self::MODE_DELETE:
                $this->storage->deleteMessage($id);
                $this->logger->log("Deleted $id", 'debug');
                break;

            default:
                throw new \InvalidArgumentException('Unvalid mode: '.$this->mode);
        }
    }
}
