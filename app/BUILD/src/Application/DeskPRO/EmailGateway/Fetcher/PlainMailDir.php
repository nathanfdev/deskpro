<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

use DeskPRO\Component\Filesystem\SafeFile;

/**
 * Reads all files in a directory as mail.
 *
 * Note that is not to be confused with MailDir (http://en.wikipedia.org/wiki/Maildir),
 * though this fetcher could be used with the 'new' directory of MailDir.
 *
 * But the point is that this does nothing about moving files to cur or setting flags.
 * It's a simple directory scanner.
 */
class PlainMailDir extends AbstractFetcher
{
    /**
     * @var string
     */
    protected $maildir;

    /**
     * @var \Directory
     */
    protected $dir;

    /**
     * @var array
     */
    protected $messageList;

    /**
     * @var int
     */
    protected $readCount = 0;

    protected function init()
    {
        if (defined('DPC_IS_CLOUD')) {
            throw new \RuntimeException();
        }

        parent::init();
    }

    /**
     * Initiates the connection.
     */
    protected function _initConnection()
    {
        $this->maildir = $this->account['connection_options']['dir'];

        $this->logger->log("Reading from: {$this->maildir}", 'debug');

        if (SafeFile::is_dir($this->maildir, SafeFile::UNSPECIFIED)) {
            $this->dir = dir($this->maildir);
        } else {
            $this->logger->log('Directory does not exist', 'debug');

            // Dir doesnt exist, but that doesnt mean error
            // Just means no mail. Checking on dir should be a separate test at setup time
            $this->dir = false;
        }

        return $this->dir;
    }

    public function __destruct()
    {
        if ($this->storage && is_resource($this->storage->handle)) {
            try {
                @$this->storage->close();
            } catch (\Exception $exception) {
                $this->logger->log(
                    "Failed to close connection to maildir: {$exception->getMessage()}",
                    'error'
                );
            }
        }

        $this->storage = null;
        $this->dir     = null;
    }

    /**
     * Read filenames from directory.
     */
    protected function _initMessageList()
    {
        if ($this->messageList !== null) {
            return $this->messageList;
        }

        $this->messageList = [];

        while (($f = $this->dir->read()) !== false) {
            if ($f != '.' && $f !== '..') {
                $this->messageList[] = $f;
            }
        }

        return $this->messageList;
    }

    /**
     * @param bool $reconnect
     *
     * @return \Directory
     */
    public function getStorage($reconnect = false)
    {
        if ($this->storage === null) {
            $this->storage = $this->_initConnection();
        }

        return $this->storage;
    }

    /**
     * Reads the next message in the inbox.
     *
     * @return \Application\DeskPRO\EmailGateway\Fetcher\RawMessage
     */
    protected function _readNext()
    {
        if (!$this->getStorage()) {
            return null;
        }

        $this->getStorage();
        $this->_initMessageList();

        ++$this->readCount;
        $this->logger->log("Trying to read next ({$this->readCount} call)", 'debug');

        $next = array_shift($this->messageList);
        if (!$next) {
            return null;
        }

        $mailfile = $this->maildir.'/'.$next;

        if (!SafeFile::is_writable($mailfile, $this->maildir)) {
            error_log("Skipping mailfile $mailfile because it is not writable so we cant delete it after");
            $this->logger->log("Skipping mailfile $mailfile because it is not writable so we cant delete it after", 'error');

            return $this->_readNext();
        }

        $messageSize = SafeFile::filesize($mailfile, $this->maildir);

        $startTime = microtime(true);

        $this->logger->log("Fetching message $next", 'debug');

        $rawMessage       = new RawMessage();
        $rawMessage->id   = $next;
        $rawMessage->size = $messageSize;

        $rawMessage->content = SafeFile::file_get_contents($mailfile, $this->maildir);
        $headers             = null;

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

        if ($this->maxSize && $rawMessage->size > $this->maxSize) {
            $rawMessage->too_big = true;
        }

        $this->logger->log(
            sprintf('Got message Took %0.2f seconds.', microtime(true) - $startTime),
            'debug'
        );

        return $rawMessage;
    }

    /**
     * Deletes the message from the server.
     *
     * @param RawMessage $rawMessage
     */
    protected function _doneRead(RawMessage $rawMessage)
    {
        $this->logger->log("Marking message as deleted: {$rawMessage->id}", 'debug');

        if (SafeFile::is_file($this->maildir.'/'.$rawMessage->id, $this->maildir) && !@SafeFile::unlink($this->maildir.'/'.$rawMessage->id, $this->maildir)) {
            sleep(1);
            if (SafeFile::is_file($this->maildir.'/'.$rawMessage->id, $this->maildir) && !SafeFile::unlink($this->maildir.'/'.$rawMessage->id, $this->maildir)) {
                $this->logger->log('Failed to delete source file: '.$this->maildir.'/'.$rawMessage->id, 'error');
            }
        }
    }

    /**
     * Tests the connection and returns the number of messages on success.
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function test()
    {
        if (!SafeFile::is_dir($this->maildir, SafeFile::UNSPECIFIED)) {
            throw new \InvalidArgumentException('Mail directory does not exist');
        }

        return 0;
    }
}
