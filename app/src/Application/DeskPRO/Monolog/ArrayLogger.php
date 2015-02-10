<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\DeskPRO\Monolog;

use Psr\Log\AbstractLogger;

class ArrayLogger extends AbstractLogger
{
    private $messages = array();

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $context['_dp_time'] = time();
        $this->messages[] = array(
            'level'   => $level,
            'message' => $message,
            'context' => $context
        );
    }

    /**
     * Get raw messages as an array
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
        $str = array();

        foreach ($this->messages as $m) {
            $str[] = "[" . date('Y-m-d H:i:s', $m['context']['_dp_time']) . "] " . $m['level'] . ": " . $m['message'];
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
