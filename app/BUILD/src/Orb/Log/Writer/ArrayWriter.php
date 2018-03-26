<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;

/**
 * This writer just saves messages to an array.
 */
class ArrayWriter extends AbstractWriter
{
    /** @var array */
    protected $messages = [];
    /** @var int */
    protected $max_size = 10000;
    /** @var int */
    protected $max_line_length = 10000;

    public function setMaxMessageLength($max_line_length = 10000)
    {
        $this->max_line_length = $max_line_length;
    }

    public function setMaxSize($max_size)
    {
        $this->max_size = $max_size;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getMessagesAsString()
    {
        return implode("\n", $this->getMessages());
    }

    public function _write(LogItem $log_item)
    {
        $msg = trim($log_item[LogItem::MESSAGE_LINE]);

        if (isset($msg[$this->max_line_length + 1])) {
            $msg = substr($msg, 0, $this->max_line_length);
        }

        $this->messages[] = $msg;

        while (isset($this->messages[$this->max_size + 1])) {
            array_shift($this->messages);
        }
    }

    public function clear()
    {
        $this->messages = [];
    }
}
