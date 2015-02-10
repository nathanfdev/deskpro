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
    private $record_ids = array();

    /**
     * @var array
     */
    private $records = array();

    /**
     * @var string
     */
    private $group_key;

    /**
     * @var int
     */
    private $max_history;

    /**
     * The thing in each record that indicates the group
     *
     * @param string $group_key  The key in each record which indicates the group to put the messages in
     * @param int $max_history   How many groups of records to keep. This helps prevent massive logs filling memory.
     */
    public function __construct($group_key, $max_history = 50)
    {
        $this->group_key   = $group_key;
        $this->max_history = $max_history;
    }

    /**
     * @param array $record
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
            $this->records[$id] = array();

            if (count($this->record_ids) > $this->max_history) {
                $old_id = array_shift($this->record_ids);
                unset($this->record_ids[$old_id]);
            }
        }

        $this->records[$id][] = $record['formatted'];
    }


    /**
     * @param string $id
     * @return string
     */
    public function getMessages($id)
    {
        return isset($this->records[$id]) ? implode("\n", $this->records[$id]) : '';
    }
}