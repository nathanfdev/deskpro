<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

use Application\DeskPRO\App;

/**
 * @see DbErrorLoggerQueue for what this is
 */
class DbErrorLogger extends Logger
{
    public function __construct()
    {
        parent::__construct();
        DbErrorLoggerQueue::initQueue();
    }

    public function logItem(\Orb\Log\LogItem $log_item)
    {
        if (!App::getDb()->isTransactionActive()) {
            DbErrorLoggerQueue::getInstance()->addBatchFlush($this, $log_item);
        } else {
            DbErrorLoggerQueue::getInstance()->add($this, $log_item);
        }
    }
}
