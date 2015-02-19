<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
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
     * Array of array(channel, message)
     * @var array
     */
    private $messages = array();

    /**
     * Array of (channel => true)
     * @var array
     */
    private $track_channels = array();

    /**
     * @var bool
     */
    private $is_tracking = false;

    /**
     * @var int
     */
    private $max_history;

    /**
     * @param int $max_history   How many messages to keep in memory before they are popped off the end
     */
    public function __construct($max_history = 5000)
    {
        $this->max_history = $max_history;
    }

    /**
     * Clear old messages
     */
    public function resetMessages()
    {
        $this->messages = array();
    }

    /**
     * Start tracking channels.
     *
     * @param array $channels
     * @param bool $clear_messages
     */
    public function startTracking(array $channels, $clear_messages = true)
    {
        $this->is_tracking = true;
        $this->track_channels = array_fill_keys($channels, true);

        if ($clear_messages) {
            $this->resetMessages();
        }
    }

    /**
     * Stop tracking channels
     */
    public function stopTracking()
    {
        $this->is_tracking = false;
        $this->track_channels = array();
    }

    /**
     * @param array $record
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
        $this->messages[] = array($record['channel'], $record['formatted']);
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        $ret = array();
        foreach ($this->messages as $x) {
            $ret[] = $x[1];
        }
        return implode("\n", $ret);
    }

    /**
     * @param array $channels
     * @return string
     */
    public function getMessagesForChannels(array $channels)
    {
        $channels = array_fill_keys($channels, true);

        $ret = array();
        foreach ($this->messages as $x) {
            if (isset($channels[$x[0]])) {
                $ret[] = $x[1];
            }
        }
        return implode("\n", $ret);
    }
}