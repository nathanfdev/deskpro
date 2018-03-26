<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * This writer just writes using error_log.
 */
class ErrorLog extends AbstractWriter
{
    public function __construct()
    {
        $this->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
    }

    /**
     * Write a message to the log.
     */
    public function _write(LogItem $log_item)
    {
        $msg = $log_item[LogItem::MESSAGE_LINE];
        error_log($msg);
    }
}
