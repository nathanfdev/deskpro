<?php

namespace DeskPRO\Bundle\ImportBundle\Monolog\Handler;

use DeskPRO\Bundle\ImportBundle\Storage\Storage;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class StorageHandler.
 */
class StorageHandler extends AbstractProcessingHandler
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var int
     */
    private $lastFlushTime;

    /**
     * @var string
     */
    private $buffer;

    /**
     * Constructor.
     *
     * @param Storage  $storage
     * @param bool|int $level
     * @param bool     $bubble
     */
    public function __construct(Storage $storage, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->storage       = $storage;
        $this->lastFlushTime = null;
    }

    /**
     * Update storage log with recent log.
     */
    public function flushLog()
    {
        if ($this->buffer) {
            $this->storage->writeLogFile($this->buffer);
        }

        $this->buffer        = '';
        $this->lastFlushTime = time();
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $flushTimeout = 3 * 60; // 3min
        $flushMaxSize = 3 * 1024 * 1024; // 3mb

        $this->buffer .= (string) $record['formatted']."\n";
        if (!$this->lastFlushTime || ((time() - $this->lastFlushTime) > $flushTimeout && strlen($this->buffer) > $flushMaxSize)) {
            $this->flushLog();
        }
    }
}
