<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365Config;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config;
use DpSys\LowError\SystemErrorHandler;

/**
 * Fetches mail from a pop3 server.
 */
class Pop3 extends AbstractFetcher implements BatchFetcher
{
    use NeedsIncomingAccountDecryptionTrait;

    /**
     * @var int
     */
    protected $readCount = 0;

    /**
     * @var array
     */
    protected $messageList = null;

    /**
     * @var array
     */
    protected $messageListIds = [];

    /**
     * @var array
     */
    protected $messageFiles = [];

    /**
     * The size in bytes a message must be before the "memory protection"
     * features are enabled.
     *
     * @var int
     */
    protected $memoryProtectionSize = 0;

    /**
     * @var bool
     */
    protected $doneReadFinished = false;

    /**
     * Files written as part of the memory protection.
     * These should be removed after a successful read.
     *
     * @var array
     */
    protected $backupFile = [];

    /**
     * @see canUniqueId()
     *
     * @var bool|null
     */
    protected $isUniqueIdCapable;

    public function init()
    {
        $this->memoryProtectionSize = 3670016;
    }

    /**
     * Initiates the connection.
     *
     * @return \Zend\Mail\Storage\Pop3
     * @throws \CannotPerformOperationException
     * @throws \InvalidArgumentException
     * @throws \InvalidCiphertextException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Zend\Mail\Protocol\Exception\RuntimeException
     * @throws \Zend\Mail\Storage\Exception\InvalidArgumentException
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
            case 'pop3':
                /** @var Pop3Config $protocolConfig */
                $protocolConfig = $incomingAccount;

                foreach (array_merge($properties, ['host', 'port', 'disable_cert_validation']) as $property) {
                    if (property_exists($protocolConfig, $property)) {
                        $options[$property] = $protocolConfig->{$property};
                    }
                }

                if (! is_null($protocolConfig->secure_mode) &&
                    in_array($protocolConfig->secure_mode, ['ssl', 'tls']))
                {
                    $options['ssl'] = strtoupper($protocolConfig->secure_mode);
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
                        'host' => 'pop.gmail.com',
                        'port' => 995,
                        'ssl'  => 'SSL',
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
                        'host' => 'outlook.office365.com',
                        'port' => 995,
                        'ssl'  => 'SSL',
                    ]
                );

                break;
            default:
                throw new \InvalidArgumentException(
                    'Unknown account type: '.$incomingAccount->getType()
                );
                break;
        }

        // pass logger to the storage
        $options['logger'] = $this->logger;

        try {
            // log attempt
            $this->logger->log(
                "Connecting to {$options['user']}@{$options['host']}:{$options['port']}",
                'debug'
            );

            // log SSL mode
            if (array_key_exists('ssl', $options)) {
                $this->logger->log(
                    "{$options['ssl']} mode enabled",
                    'debug'
                );
            }

            // attempt to connect
            $storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3(
                array_merge($options, ['logger' => $this->logger])
            );

            // log success
            $this->logger->log(
                "Connected to {$options['user']}@{$options['host']}:{$options['port']}",
                'debug'
            );
        } catch (\Zend\Mail\Protocol\Exception\RuntimeException $exception) {
            // log failure
            $this->logger->log(
                "An error has occured while setting up connection: {$exception->getMessage()}",
                'error'
            );

            throw $exception;
        }

        // return storage
        return $storage;
    }

    /**
     * Close connection
     */
    public function close()
    {
        if ($this->storage) {
            $this->storage->close();
            $this->storage = null;
            $this->logger->log('Connection closed', 'debug');
        }
    }

    public function resetConnection()
    {
        $this->logger->log('Resetting connection', 'debug');
        $this->getStorage(true);
        $this->_initMessageList(true);
    }

    /**
     * Server supports uniqid?
     *
     * @return mixed
     */
    protected function canUniqueId()
    {
        if ($this->isUniqueIdCapable === null) {
            try {
                $this->isUniqueIdCapable = $this->getStorage()->canUniqueId();
            } catch (\Exception $e) {
                $this->isUniqueIdCapable = false;
            }
        }

        return $this->isUniqueIdCapable;
    }

    /**
     * Get a list of message IDs.
     *
     * @param bool $reload
     */
    protected function _initMessageList($reload = false)
    {
        if (!$reload && $this->messageList !== null) {
            return;
        }

        if ($this->account->getOption('keep_read')) {
            if (!$this->canUniqueId()) {
                try {
                    $capas = $this->getStorage()->getProtocolCapabilities();
                    $capas = implode(', ', $capas);
                } catch (\Exception $e) {
                    $capas = '<unknown>';
                }
                $this->logger->log(
                    "Email account does not support unique but keep_read is enabled. Capabilities: {$capas}",
                    'debug'
                );

                $exception             = new \InvalidArgumentException('Email account does not support uniqueid');
                $info                  = SystemErrorHandler::getExceptionInfo($exception);
                $info['no_send_error'] = true;
                SystemErrorHandler::logErrorInfo($info);

                $this->messageList = [];

                return;
            }

            $idToNum = array_flip($this->getStorage()->getUniqueId());

            $this->logger->log(
                'Server has '.count($idToNum).' messages',
                'debug'
            );

            if (count($idToNum) > 2500) {
                $this->logger->log('Server has >= 2500 messages, breaking', 'ERR');
                $this->messageList = [];

                $exception             = new \InvalidArgumentException("POP3 server has >= 2500 messages and 'keep read' setting is enbaled. Clean out old messages and try again.");
                $info                  = SystemErrorHandler::getExceptionInfo($exception);
                $info['no_send_error'] = true;
                SystemErrorHandler::logErrorInfo($info);

                return;
            }

            $readIds = App::getDb()->fetchAllCol('
                SELECT id
                FROM email_uids
                WHERE email_account_id = ?
            ', [$this->account->getId()]);

            $this->logger->log(
                'System has '.count($readIds).' tracked IDs',
                'debug'
            );

            foreach ($readIds as $id) {
                if (isset($idToNum[$id])) {
                    $this->logger->log(
                        sprintf('Skipping message #%s because UID %s', $idToNum[$id], $id),
                        'debug'
                    );
                    unset($idToNum[$id]);
                }
            }

            $this->messageListIds = array_flip($idToNum);

            $list = $this->getStorage()->getSize();

            $this->messageList = [];
            foreach ($list as $num => $size) {
                if (isset($this->messageListIds[$num])) {
                    $this->messageList[] = ['num' => $num, 'size' => $size, 'uid' => $this->messageListIds[$num]];
                }
            }

            $this->logger->log(
                'Message list contains '.count($this->messageList).' new messages',
                'debug'
            );
        } else {
            $list = $this->getStorage()->getSize();

            $this->messageList = [];
            foreach ($list as $num => $size) {
                $this->messageList[] = ['num' => $num, 'size' => $size, 'uid' => null];
            }

            $this->logger->log(
                'Message list contains '.count($this->messageList).' messages',
                'debug'
            );
        }
    }

    /**
     * Reads the next message in the inbox.
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     * @throws \Exception
     */
    protected function _readNext()
    {
        $this->doneReadFinished = null;

        $this->getStorage();
        $this->_initMessageList();

        ++$this->readCount;
        $this->logger->log("Trying to read next ({$this->readCount} call)", 'debug');

        $next = array_shift($this->messageList);
        if (!$next) {
            return;
        }

        $messageSize = $next['size'];
        $messageNum  = $next['num'];
        $messageId   = $next['uid'];

        // Memory protection enables writing the email to a file and then immediately
        // deleting it from the server (which causes a server disconnect/reconnect).
        // So if theres a crash, the same message wont be attempted next run and hold
        // up all other messages.
        $memoryProtection = false;
        if ($this->memoryProtectionSize && $messageSize >= $this->memoryProtectionSize) {
            $memoryProtection = true;
        }

        if (!$messageId && $this->canUniqueId()) {
            try {
                $messageId = $this->getStorage()->getProtocol()->uniqueid($messageNum);
            } catch (\Exception $e) {
            }
        }

        // If we have a uid and this server has unique ids,
        // then detect an edge case where we've already ready the id
        // but it wasnt properly deleted
        if ($messageId && $this->canUniqueId()) {
            $count = App::getDb()->fetchColumn('
                SELECT COUNT(*)
                FROM email_uids
                WHERE id = ? AND email_account_id = ?
            ', [$messageId, $this->account->getId()]);
            if ($count) {
                $this->_doneRead($messageNum);

                return $this->_readNext();
            }
        }

        $startTime = microtime(true);

        $this->logger->log(
            "Fetching message #{$messageNum}",
            'debug'
        );

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $messageNum;
        $rawMessage->uid  = $messageId;
        $rawMessage->size = $messageSize;

        if ($this->maxSize && $rawMessage->size && $rawMessage->size > $this->maxSize) {
            $rawMessage->content = $this->getStorage()->getProtocol()->top($messageNum)."\n\n";
        } else {
            if ($memoryProtection) {
                $this->logger->log('Memory protected enabled', 'info');
                $contentFile = dp_get_backup_dir().'/eml-'.uniqid('', true).'.eml';
                $fp          = fopen($contentFile, 'w');
                if ($fp) {
                    $this->getStorage()->getProtocol()->retrieveToStream($messageNum, $fp);
                    fclose($fp);

                    $this->_doneRead($messageNum);
                    $this->doneReadFinished = $messageNum;

                    // Disconnect from server so message is deleted now
                    $this->resetConnection();
                } else {
                    $memoryProtection = false;
                    $e                = new \RuntimeException("Could not save email backup file to {$rawMessage->content_file}");
                    SystemErrorHandler::logException($e, false);
                }

                $this->logger->log('Message source saved to: '.$contentFile, 'debug');
                $rawMessage->content = file_get_contents($contentFile);
                $this->backupFile    = $contentFile;
            }

            if (!$memoryProtection) {
                $rawMessage->content = $this->getStorage()->getProtocol()->retrieve($messageNum);
            }
        }
        $headers = null;

        $this->logger->log(sprintf('Message size: %s bytes', $messageSize), 'debug');

        if ($rawMessage->uid) {
            $this->logger->log(sprintf('Message UID: %s', $rawMessage->uid), 'debug');
        }

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

        if (!$rawMessage->size) {
            $rawMessage->size = strlen($rawMessage->content);
        }

        if ($this->maxSize && $rawMessage->size > $this->maxSize) {
            $rawMessage->too_big = true;
            $this->logger->log('Setting too_big flag', 'debug');
        }

        $this->logger->log(sprintf('Got message %d %s. Took %0.2f seconds.', $messageNum, $messageId, microtime(true) - $startTime), 'debug');

        return $rawMessage;
    }

    /**
     * Deletes the message from the server.
     *
     * @param  $id
     * @throws \Exception
     */
    protected function _doneRead($id)
    {
        if ($this->backupFile) {
            unlink($this->backupFile);
            $this->backupFile = null;
        }

        if ($this->doneReadFinished !== null && $this->doneReadFinished == $id) {
            return;
        }

        if ($this->account->getOption('keep_read')) {
            $this->logger->log(sprintf('Done read, but keep_read is enabled'), 'debug');

            return;
        }

        $this->logger->log("Marking message as deleted: $id", 'debug');
        try {
            $this->getStorage()->removeMessage($id);
        } catch (\Exception $e) {
            $this->logger->log("Exception: {$e->getMessage()} {$e->getTraceAsString()}", 'crit');

            throw $e;
        }
    }

    /**
     * Tests the connection and returns the number of messages on success.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function test()
    {
        try {
            $x = $this->getStorage()->countMessages();
        } catch (\Exception $e) {
            throw $e;
        }

        return $x;
    }

    /**
     * @param string $object_type
     * @param int $limit
     *
     * @return \Application\DeskPRO\Entity\EmailSource[]
     * @throws \Exception
     */
    public function readBatch($object_type = 'ticket', $limit = 10)
    {
        $sources = [];

        while ($limit-- > 0) {
            $s = $this->readNext($object_type);
            if (!$s) {
                break;
            }

            $sources[] = $s;
        }

        // closes conn to commit any dele's
        $this->close();

        return $sources;
    }
}
