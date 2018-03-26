<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Monolog\Processor;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * This just keeps a record of messages grouped by something in the record data.
 *
 * This is like GroupRecordsProcessor except, well, its a handler and not a processor.
 * Use the processor if you need access to raw messages, and use this if you just
 * care about the log lines.
 */
class GroupRecordsHandler extends AbstractProcessingHandler
{
    /**
     * @var string[]
     */
    private $record_ids = [];

    /**
     * @var array
     */
    private $records = [];

    /**
     * @var string
     */
    private $group_key;

    /**
     * @var int
     */
    private $max_history;

    /**
     * The thing in each record that indicates the group.
     *
     * @param string $group_key   The key in each record which indicates the group to put the messages in
     * @param int    $max_history How many groups of records to keep. This helps prevent massive logs filling memory
     */
    public function __construct($group_key, $max_history = 50)
    {
        $this->group_key   = $group_key;
        $this->max_history = $max_history;
    }

    /**
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record)
    {
        if (!isset($record[$this->group_key]) && !isset($record['extra'][$this->group_key])) {
            return false;
        }

        return parent::isHandling($record);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $id = isset($record[$this->group_key]) ? $record[$this->group_key] : null;
        if ($id === null) {
            $id = isset($record['extra'][$this->group_key]) ? $record['extra'][$this->group_key] : null;
        }

        if ($id === null) {
            return;
        }

        if (!isset($this->records[$id])) {
            $this->record_ids[] = $id;
            $this->records[$id] = [];

            if (count($this->record_ids) > $this->max_history) {
                $old_id = array_shift($this->record_ids);
                unset($this->record_ids[$old_id]);
            }
        }

        $this->records[$id][] = $record['formatted'];
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function getMessages($id)
    {
        return isset($this->records[$id]) ? implode("\n", $this->records[$id]) : '';
    }
}
