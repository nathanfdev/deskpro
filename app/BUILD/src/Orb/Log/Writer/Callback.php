<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * This writer passes the log item to any callback.
 */
class Callback extends AbstractWriter
{
    /** @var callable */
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Write a message to the log.
     */
    public function _write(LogItem $log_item)
    {
        call_user_func($this->callback, $log_item);
    }
}
