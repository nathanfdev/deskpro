<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
        $this->lastFlushTime = time();
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
        if ((time() - $this->lastFlushTime) > $flushTimeout && strlen($this->buffer) > $flushMaxSize) {
            $this->flushLog();
        }
    }
}
