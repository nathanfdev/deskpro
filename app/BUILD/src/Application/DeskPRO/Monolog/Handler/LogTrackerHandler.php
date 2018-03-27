<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Monolog\Processor;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * This is a way to keep track of log messages for certain channels.
 *
 * This is useful if you want to store a log of some operation after. For example, when processing an email,
 * there are a number of different components involved that you want to keep to save for debugging purposes.
 */
class LogTrackerHandler extends AbstractProcessingHandler
{
    /**
     * Array of array(channel, message).
     *
     * @var array
     */
    private $messages = [];

    /**
     * Array of (channel => true).
     *
     * @var array
     */
    private $track_channels = [];

    /**
     * @var bool
     */
    private $is_tracking = false;

    /**
     * @var int
     */
    private $max_history;

    /**
     * @param int $max_history How many messages to keep in memory before they are popped off the end
     */
    public function __construct($max_history = 5000)
    {
        $this->max_history = $max_history;
    }

    /**
     * Clear old messages.
     */
    public function resetMessages()
    {
        $this->messages = [];
    }

    /**
     * Start tracking channels.
     *
     * @param array $channels
     * @param bool  $clear_messages
     */
    public function startTracking(array $channels, $clear_messages = true)
    {
        $this->is_tracking    = true;
        $this->track_channels = array_fill_keys($channels, true);

        if ($clear_messages) {
            $this->resetMessages();
        }
    }

    /**
     * Stop tracking channels.
     */
    public function stopTracking()
    {
        $this->is_tracking    = false;
        $this->track_channels = [];
    }

    /**
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record)
    {
        if (!parent::isHandling($record)) {
            return false;
        }
        if (!$this->is_tracking) {
            return false;
        }
        if (!isset($this->track_channels['*']) && !isset($this->track_channels[$record['channel']])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $this->messages[] = [$record['channel'], $record['formatted']];
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        $ret = [];
        foreach ($this->messages as $x) {
            $ret[] = $x[1];
        }

        return implode("\n", $ret);
    }

    /**
     * @param array $channels
     *
     * @return string
     */
    public function getMessagesForChannels(array $channels)
    {
        $channels = array_fill_keys($channels, true);

        $ret = [];
        foreach ($this->messages as $x) {
            if (isset($channels[$x[0]])) {
                $ret[] = $x[1];
            }
        }

        return implode("\n", $ret);
    }
}
