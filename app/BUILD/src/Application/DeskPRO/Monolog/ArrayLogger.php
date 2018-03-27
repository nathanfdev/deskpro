<?php

namespace Application\DeskPRO\Monolog;

use Psr\Log\AbstractLogger;

class ArrayLogger extends AbstractLogger
{
    private $messages = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        $context['_dp_time'] = time();
        $this->messages[]    = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    /**
     * Get raw messages as an array.
     *
     * @return array
     */
    public function getRawMessages()
    {
        return $this->messages;
    }

    /**
     * Get all messages as a string.
     *
     * @return string
     */
    public function toString()
    {
        $str = [];

        foreach ($this->messages as $m) {
            $str[] = '['.date('Y-m-d H:i:s', $m['context']['_dp_time']).'] '.$m['level'].': '.$m['message'];
        }

        return implode("\n", $str);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
