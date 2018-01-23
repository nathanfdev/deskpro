<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
use DpSys\LowError\SystemErrorHandler;

/**
 * Fetches mail from a pop3 server.
 */
class Pop3 extends AbstractFetcher implements BatchFetcher
{
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
     */
    protected function _initConnection()
    {
        $options = [];

        $incomingAccount = EmailAccountUtil::decryptIncomingAccount($this->account->incoming_account, App::$container->get('dp_enc'));

        switch ($incomingAccount->getType()) {
            case 'pop3':
                /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config $pop3Config */
                $pop3Config = $incomingAccount;

                $options['host']                    = $pop3Config->host;
                $options['port']                    = $pop3Config->port;
                $options['user']                    = $pop3Config->user;
                $options['password']                = $pop3Config->password;
                $options['disable_cert_validation'] = $pop3Config->disable_cert_validation;

                $this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

                if ($pop3Config->secure_mode == 'ssl') {
                    $options['ssl'] = 'SSL';
                    $this->logger->log('SSL Enabled', 'debug');
                } elseif ($pop3Config->secure_mode == 'tls') {
                    $options['ssl'] = 'TLS';
                    $this->logger->log('TLS Enabled', 'debug');
                }
                break;

            case 'gmail':
                /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig $gmailConfig */
                $gmailConfig = $incomingAccount;

                $options['host']     = 'pop.gmail.com';
                $options['port']     = 995;
                $options['user']     = $gmailConfig->user;
                $options['password'] = $gmailConfig->password;

                $this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

                $options['ssl'] = 'SSL';
                $this->logger->log('SSL Enabled', 'debug');
                break;

            case 'office365':
                /** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365Config $config */
                $config = $incomingAccount;

                $options['host']     = 'outlook.office365.com';
                $options['port']     = 995;
                $options['user']     = $config->user;
                $options['password'] = $config->password;

                $this->logger->log("Connecting with user {$options['user']} to {$options['host']}:{$options['port']}", 'debug');

                $options['ssl'] = 'SSL';
                $this->logger->log('SSL Enabled', 'debug');
                break;

            default:
                throw new \InvalidArgumentException('Unknown account type: '.$incomingAccount->getType());
        }

        $options['logger'] = $this->logger;

        $storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3($options);

        return $storage;
    }

    public function close()
    {
        if ($this->storage) {
            $this->storage->close();
            $this->storage = null;
        }
    }

    public function resetConnection()
    {
        $this->logger->logDebug('Resetting connection');
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
                $this->logger->log("Email account does not support unique but keep_read is enabled. Capabilities: $capas", 'debug');

                $e                      = new \InvalidArgumentException('Email account does not support uniqueid');
                $einfo                  = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
                $einfo['no_send_error'] = true;
                \DpSys\LowError\SystemErrorHandler::logErrorInfo($einfo);

                $this->messageList = [];

                return;
            }

            $idToNum = array_flip($this->getStorage()->getUniqueId());

            $this->logger->log('Server has '.count($idToNum).' messages', 'debug');

            if (count($idToNum) > 2500) {
                $this->logger->log('Server has >= 2500 messages, breaking', 'ERR');
                $this->messageList = [];

                $e                      = new \InvalidArgumentException("POP3 server has >= 2500 messages and 'keep read' setting is enbaled. Clean out old messages and try again.");
                $einfo                  = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
                $einfo['no_send_error'] = true;
                \DpSys\LowError\SystemErrorHandler::logErrorInfo($einfo);

                return;
            }

            $readIds = App::getDb()->fetchAllCol('
                SELECT id
                FROM email_uids
                WHERE email_account_id = ?
            ', [$this->account->getId()]);

            $this->logger->log('System has '.count($readIds).' tracked IDs', 'debug');

            foreach ($readIds as $id) {
                if (isset($idToNum[$id])) {
                    $this->logger->log(sprintf('Skipping message #%s because UID %s', $idToNum[$id], $id), 'debug');
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

            $this->logger->log('Message list contains '.count($this->messageList).' new messages', 'debug');
        } else {
            $list = $this->getStorage()->getSize();

            $this->messageList = [];
            foreach ($list as $num => $size) {
                $this->messageList[] = ['num' => $num, 'size' => $size, 'uid' => null];
            }

            $this->logger->log('Message list contains '.count($this->messageList).' messages', 'debug');
        }
    }

    /**
     * Reads the next message in the inbox.
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
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

        $this->logger->log("Fetching message #$messageNum", 'debug');

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $messageNum;
        $rawMessage->uid  = $messageId;
        $rawMessage->size = $messageSize;

        if ($this->maxSize && $rawMessage->size && $rawMessage->size > $this->maxSize) {
            $rawMessage->content = $this->getStorage()->getProtocol()->top($messageNum)."\n\n";
        } else {
            if ($memoryProtection) {
                $this->logger->logInfo('Memory protected enabled');
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

                $this->logger->logInfo('Message source saved to: '.$contentFile);
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
     * @param int    $limit
     *
     * @return \Application\DeskPRO\Entity\EmailSource[]
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
