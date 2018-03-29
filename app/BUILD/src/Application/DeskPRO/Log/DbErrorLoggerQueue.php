<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

use Application\DeskPRO\App;

class DbErrorLoggerQueue
{
    /** @var array */
    protected $waiting = [];

    private function __construct()
    {
    }

    /**
     * Get the single instance of the queue.
     *
     * @return DbErrorLoggerQueue
     */
    public static function getInstance()
    {
        static $inst;
        if (!$inst) {
            $inst = new self();
        }

        return $inst;
    }

    /**
     * Inits the queue once.
     */
    public static function initQueue()
    {
        static $has_init;
        if (!$has_init) {
            App::getDb()->getEventManager()->addEventListener([
                'onPostCommit', 'onPostRollback',
            ], self::getInstance());

            \DpShutdown::add(function () {
                DbErrorLoggerQueue::getInstance()->flush();
            });
        }
    }

    /**
     * Adds a queued log.
     *
     * @param $logger
     * @param $item
     */
    public function add($logger, $item)
    {
        $this->waiting[] = [$logger, $item];
    }

    /**
     * Adds a log message.
     *
     * @param $logger
     * @param $item
     */
    public function addBatchFlush($logger, $item)
    {
        $this->waiting[] = [$logger, $item];

        if (isset($this->waiting[50])) {
            $this->flush();
        }
    }

    /**
     * Flushes all waiting logs to be written.
     */
    public function flush()
    {
        if (!$this->waiting) {
            return;
        }

        $loggers = [];

        foreach ($this->waiting as $info) {
            $logger = $info[0];
            $item   = $info[1];

            $logger->logItem($item);
            $loggers[spl_object_hash($logger)] = $logger;
        }

        foreach ($loggers as $l) {
            $l->flush();
        }

        $this->waiting = [];
    }

    /**
     * @see Application\DeskPRO\DBAL\Connection::commit
     */
    public function onPostCommit()
    {
        try {
            $this->flush();
        } catch (\Exception $e) {
        }
    }

    /**
     * @see Application\DeskPRO\DBAL\Connection::rollback
     */
    public function onPostRollback()
    {
        try {
            $this->flush();
        } catch (\Exception $e) {
        }
    }
}
