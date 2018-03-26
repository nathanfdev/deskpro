<?php

/**
 * Orb.
 */

namespace Orb\Log;

/**
 * A specific log event.
 */
class LogItem implements \IteratorAggregate, \ArrayAccess
{
    /**@#+ Standard fields **/
    const PRIORITY      = 'priority';
    const PRIORITY_NAME = 'priority_name';
    const MESSAGE       = 'message';
    const MESSAGE_LINE  = 'message_line';
    const DATETIME      = 'datetime';
    const SESSION_NAME  = 'session_name';
    /**@#-*/

    /**
     * @var array
     */
    protected $_standard_fields = [
        self::PRIORITY,
        self::PRIORITY_NAME,
        self::MESSAGE,
        self::MESSAGE_LINE,
        self::DATETIME,
        self::SESSION_NAME,
    ];

    /**
     * @var array
     */
    protected $info = [];

    public function __construct(array $info)
    {
        if ($info) {
            $this->info = $info;
        }

        if (!isset($this->info[self::DATETIME])) {
            $this->info[self::DATETIME] = new \DateTime();
        }
        if (!isset($this->info[self::PRIORITY])) {
            $this->info[self::PRIORITY] = Logger::INFO;
        }
        if (!isset($this->info[self::PRIORITY_NAME])) {
            $this->info[self::PRIORITY_NAME] = $this->info[self::PRIORITY];
        }
        if (!isset($this->info[self::MESSAGE])) {
            $this->info[self::MESSAGE] = '';
        }
        if (!isset($this->info[self::MESSAGE_LINE])) {
            $this->info[self::MESSAGE_LINE] = $this->info[self::MESSAGE];
        }
        if (!isset($this->info[self::SESSION_NAME])) {
            $this->info[self::SESSION_NAME] = null;
        }

        $this->init();
    }

    /**
     * Empty init method for children.
     */
    protected function init()
    {
    }

    /**
     * Get the numeric priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->info[self::PRIORITY];
    }

    /**
     * Get the priority name.
     *
     * @return string
     */
    public function getPriorityName()
    {
        return $this->info[self::PRIORITY_NAME];
    }

    /**
     * Get the log message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->info[self::MESSAGE];
    }

    /**
     * Get the message line. This may simply be the message, but it may have been
     * transformed with other information. Generally a transformer will leave message
     * original and just transform this. For example, to log to a file you may
     * want to include priority name, timestamp etc in the line.
     *
     * @return string
     */
    public function getMessageLine()
    {
        return $this->info[self::MESSAGE_LINE];
    }

    /**
     * Get the time of the event.
     *
     * @return DateTime
     */
    public function getDatetime()
    {
        return $this->info[self::DATETIME];
    }

    /**
     * Get the session name.
     *
     * @return string
     */
    public function getSessionName()
    {
        return $this->info[self::SESSION_NAME];
    }

    /**
     * Get extra, non-standard event data.
     */
    public function getExtra()
    {
        $ret = [];

        foreach ($this->info as $k => $v) {
            if (!in_array($k, $this->_standard_fields)) {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    public function toArray()
    {
        return $this->info;
    }

    /**@#+ ArrayAccess implementation */
    public function offsetSet($offset, $value)
    {
        $this->info[$offset] = $value;
    }
    public function offsetExists($offset)
    {
        return isset($this->info[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->info[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->info[$offset];
    }
    /**@#-*/

    public function getIterator()
    {
        return new \ArrayIterator($this->info);
    }
}
