<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Orb\Log\Logger;
use Orb\Util\Strings;

/**
 * A fetcher takes makes a conenction to a resource described in
 * an EmailAccount record, and reads messages into the database for storage.
 */
abstract class AbstractFetcher
{
    /**
     * \Application\DeskPRO\EmailAccount.
     */
    protected $account;

    /**
     * @var \Zend\Mail\Storage\AbstractStorage
     */
    protected $storage;

    /**
     * @var \Application\DeskPRO\Log\Logger
     */
    protected $logger;

    /**
     * The max size in byes to read.
     *
     * @var int
     */
    protected $maxSize = 0;

    /**
     * @param \Application\DeskPRO\Entity\EmailAccount $account
     * @param int                                      $maxSize The max size in bytes to read. 0 to disable
     */
    public function __construct(EmailAccount $account, $maxSize = 0)
    {
        $this->account = $account;
        $this->logger  = new Logger();
        $this->setMaxSize($maxSize);
        $this->init();
    }

    protected function init()
    {
    }

    public function __destruct()
    {
        if ($this->storage) {
            try {
                $this->storage->close();
            } catch (\Exception $exception) {
            }
        }
    }

    /**
     * Closes the connection.
     */
    protected function _closeConnection()
    {
        if ($this->storage) {
            try {
                $this->storage->close();
            } catch (\Exception $exception) {
            }
        }
    }

    /**
     * Set the max size to read.
     *
     * @param int $maxSize The max size in bytes
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = (int) $maxSize;
        if ($this->maxSize < 0) {
            $this->maxSize = 0;
        }
    }

    /**
     * Get the max size.
     *
     * @return int
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param bool $reconnect
     *
     * @return mixed
     */
    public function getStorage($reconnect = false)
    {
        if ($reconnect && $this->storage) {
            $this->_closeConnection();
            $this->storage = null;
        }

        if (!$this->storage) {
            $this->storage = $this->_initConnection();
        }

        return $this->storage;
    }

    /**
     * Closes the fetcher.
     */
    public function close()
    {
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Initiates the connection.
     *
     * @return \Zend\Mail\Storage\AbstractStorage
     */
    abstract protected function _initConnection();

    /**
     * Reads the next message in the inbox. Must return
     * a RawMessage.
     *
     * Return null if there are no more messages.
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    abstract protected function _readNext();

    /**
     * Marks the email as 'read' in any way that'll prevent the system from
     * reading it again. For example, deleting it, moving it to a new folder, etc.
     *
     * @param  $id
     */
    abstract protected function _doneRead($id);

    /**
     * Reads the next message from the resource and saves it into the datbaase,
     * then removes the email so its not read again.
     *
     * Returns null if there are no more messages.
     *
     * @param string $object_type
     *
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Entity\EmailSource
     */
    public function readNext($object_type = 'ticket')
    {
        try {
            $rawMessage = $this->_readNext();
        } catch (\Exception $exception) {
            $this->logger->log(sprintf('_readNext exception: %s', $exception->getMessage()), 'debug');
            if ($this->storage) {
                try {
                    $this->storage->close();
                } catch (\Exception $exception) {
                }
            }
            throw $exception;
        }

        if (!$rawMessage) {
            return null;
        }

        // Protection against nulls
        if (!$rawMessage->headers) {
            $rawMessage->headers = '';
        }
        if (!$rawMessage->content) {
            $rawMessage->content = '';
        }

        App::getOrm()->beginTransaction();

        try {
            //------------------------------
            // Store the message
            //------------------------------

            $source = new EmailSource();
            $source->fromArray([
                'email_account' => $this->account,
                'headers'       => $rawMessage->headers,
                'status'        => 'inserted',
            ]);

            // Rough matching, just for info purposes when browsing a list
            $source->header_to      = Strings::extractRegexMatch('#^To:\s*(.*?)$#m', $rawMessage->headers) ?: '';
            $source->header_cc      = Strings::extractRegexMatch('#^Cc:\s*(.*?)$#m', $rawMessage->headers) ?: '';
            $source->header_from    = Strings::extractRegexMatch('#^From:\s*(.*?)$#m', $rawMessage->headers) ?: '';
            $source->header_subject = Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $rawMessage->headers) ?: '';
            $source->object_type    = $object_type;

            if ($rawMessage->uid) {
                $source->uid = $rawMessage->uid;

                App::getDb()->executeUpdate('
                    INSERT IGNORE INTO email_uids
                    SET id = ?, email_account_id = ?, date_created = ?
                ', [$rawMessage->uid, $this->account->getId(), date('Y-m-d H:i:s')]);

                $this->logger->log(sprintf('Saved UID: %s', $rawMessage->uid), 'debug');
            }

            if ($rawMessage->too_big) {
                $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
                    $rawMessage->content,
                    'email.eml',
                    'message/rfc822'
                );
                // Unset the content now, its not used from here on out
                $rawMessage->content = '';

                $source->status      = 'error';
                $source->error_code  = EmailSource::ERR_MESSAGE_TOO_BIG;
                $source->source_info = [
                    'size'     => $rawMessage->size,
                    'max_size' => $this->maxSize,
                ];
            } else {
                $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromString(
                    $rawMessage->content,
                    'email.eml',
                    'message/rfc822'
                );
            }

            $source->blob = $blob;

            App::getOrm()->persist($source);
            App::getOrm()->flush();

            App::getOrm()->commit();

            $this->logger->log(sprintf('Committed message source: %s', $source->getId()), 'debug');

            //------------------------------
            // Delete message on the server
            //------------------------------

            $this->_doneRead($rawMessage->id);
        } catch (\Exception $exception) {
            $this->logger->log(sprintf('Save source error: %s', $exception->getMessage()), 'debug');
            App::getOrm()->rollback();
            if ($this->storage) {
                try {
                    $this->storage->close();
                } catch (\Exception $exception) {
                }
            }
            throw $exception;
        }

        $source->_raw = $rawMessage->content;

        return $source;
    }

    /**
     * Test the resource to see if configuration is correct and/or that the service
     * supports the required features.
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }
}
