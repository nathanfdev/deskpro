<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountUtil;
use Application\DeskPRO\EmailGateway\Storage;

/**
 * Fetches mail from an exchange server.
 */
class Exchange extends AbstractFetcher
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
     * @var \Application\DeskPRO\EmailGateway\Storage\Exchange
     */
    protected $storage;

    /**
     * Messages retrieved in the current fetch.
     *
     * @var array An array of messages
     */
    protected $messages;

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
     * Max number of email IDs to fetch in one go.
     *
     * @var int
     */
    protected $fetchLimit = 100;

    /**
     * Next Message index to read.
     *
     * @var int
     */
    protected $nextIndex = 0;

    /**
     * Initiates the connection.
     *
     * @return \Zend\Mail\Storage\Pop3
     */
    protected function _initConnection()
    {
        $options = [];

        $incomingAccount = EmailAccountUtil::decryptIncomingAccount($this->account->incoming_account, App::$container->get('dp_enc'));

        switch ($incomingAccount->getType()) {
            case 'exchange':
                /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig $exchangeConfig */
                $exchangeConfig = $incomingAccount;

                $options['host']         = $exchangeConfig->host;
                $options['port']         = $exchangeConfig->port;
                $options['user']         = $exchangeConfig->user;
                $options['password']     = $exchangeConfig->password;
                $options['mode']         = $exchangeConfig->mode;
                $options['read_mailbox'] = $exchangeConfig->read_mailbox;

                if ($exchangeConfig->mode == self::MODE_ARCHIVE) {
                    $options['archive_mailbox'] = $exchangeConfig->archive_mailbox;
                }

                break;

            default:
                throw new \InvalidArgumentException('Unknown account type: '.$incomingAccount->getType());
        }

        $this->mode           = $options['mode'];
        $this->archiveMailbox = !empty($options['archive_mailbox']) ? $options['archive_mailbox'] : 'DP_Archive';
        $this->readMailbox    = !empty($options['read_mailbox']) ? $options['read_mailbox'] : null;

        $this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');
        $options['logger'] = $this->logger;

        $this->storage = new Storage\Exchange($options);

        if ($this->mode == self::MODE_ARCHIVE) {
            $this->storage->ensureFolderExists($this->archiveMailbox);
        }

        if ($this->readMailbox) {
            $this->storage->ensureFolderExists($this->readMailbox);
        }

        $unreadOnly = false;
        $folder     = null;

        if ($this->mode == self::MODE_READ) {
            $unreadOnly = true;
        }
        if ($this->readMailbox) {
            $folder = $this->readMailbox;
        }

        $this->messages = $this->storage->searchIds($this->fetchLimit, $unreadOnly, $folder);
        if (!$this->messages) {
            $this->messages = [];
        }

        $this->logger->log(sprintf('Read %d messages', count($this->messages)), 'debug');

        return $this->storage;
    }

    /**
     * Gets the message storage.
     *
     * @param bool $reconnect
     *
     * @return Storage\Exchange
     */
    public function getStorage($reconnect = false)
    {
        return $this->storage;
    }

    /**
     * Gets the next message
     * Iterates over the fetched IDs and retrieves the next message in list.
     *
     * @return object
     */
    public function getNextMessage()
    {
        return $this->storage->getEmailParts($this->messages[$this->nextIndex]);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    public function _readNext()
    {
        try {
            return $this->_doReadNext();
        } catch (\Exception $e) {
            $this->logger->logError(sprintf('Exchange error: <%s> [%s] %s', get_class($e), $e->getCode(), $e->getMessage()));
            $this->logger->logDebug($e->getTraceAsString());
            $this->logger->logInfo('Last response: '.$this->storage->getLastResponse());
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    private function _doReadNext()
    {
        if (!$this->storage) {
            $this->_initConnection();
        }

        if (!isset($this->messages[$this->nextIndex])) {
            return false;
        }

        $messageId = $this->messages[$this->nextIndex];

        ++$this->nextIndex;

        $message = $this->storage->getEmailProps($messageId);

        if (!$message) {
            return null;
        }

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $messageId;
        $rawMessage->uid  = $messageId;
        $rawMessage->size = $message->Size;

        if ($this->maxSize && $rawMessage->size && $rawMessage->size > $this->maxSize) {
            // If we are here, it means that message is larger than the max size
            // So, we won't store the whole message, only the headers.
            $rawMessage->content = $this->storage->getRawHeaders($message);
            $rawMessage->too_big = true;
            $this->logger->log('Setting too_big flag', 'debug');
        } else {
            // Otherwise store the whole message
            $rawMessage->content = $this->storage->getRawMessage($messageId);
        }

        $headers = null;

        $this->logger->log(sprintf('Message size: %s bytes', $rawMessage->size), 'debug');

        if ($rawMessage->uid) {
            $this->logger->log(sprintf('Message UID: %s', $rawMessage->uid), 'debug');
        }

        $EOL = "\n";

        // Reads and formats the Message header
        // To be compatible with the RawMessage
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
            case self::MODE_DELETE:
                $this->storage->deleteMessage($id);
                break;
            case self::MODE_ARCHIVE:
                $this->storage->moveMessage($id, $this->archiveMailbox);
                break;
            case self::MODE_READ:
                $this->storage->markRead($id);
                break;
        }
    }
}
