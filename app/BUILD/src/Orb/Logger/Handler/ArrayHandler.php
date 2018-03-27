<?php

/**
 * Orb.
 *
 * @category Logger
 */

namespace Orb\Logger\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class ArrayHandler extends AbstractHandler
{
    /** @var int */
    protected $max_size = 5000;
    /** @var array */
    protected $messages = [];
    /** @var int */
    protected $count = 0;

    public function __construct($max_size = 0, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->max_size = $max_size;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        if ($this->max_size > 0 && $this->max_size === $this->count) {
            array_shift($this->messages);
            --$this->count;
        }

        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        $this->messages[] = $this->getFormatter()->format($record);
        ++$this->count;

        return false === $this->bubble;
    }

    /**
     * Resets messages to empty.
     */
    public function reset()
    {
        $this->messages = [];
        $this->count    = 0;
    }

    /**
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return string
     */
    public function getMessagesAsString()
    {
        return implode("\n", $this->messages);
    }
}
