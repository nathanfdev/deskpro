<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;

/**
 * Performs groupings for specific tickets only.
 */
class SimpleGroupingCounter extends GroupingCounter
{
    /** @var array */
    protected $_ticket_ids = [];

    public function __construct(array $ticket_ids, $group_by)
    {
        $this->_ticket_ids = $ticket_ids;
        $this->setGrouping($group_by);
    }

    public function getCounts()
    {
        $group_by = 'GROUP BY field1';
        $db       = App::getDb();

        $select_fields[] = $db->quoteIdentifier('tickets.'.$this->grouping1).' AS field1';
        if ($this->grouping2) {
            $select_fields[] = $db->quoteIdentifier('tickets.'.$this->grouping2).' AS field2';
            $group_by .= ', field2';
        }
        $select_fields[] = 'COUNT(*) AS total';

        $wheres = ['tickets.id IN (?)'];

        $sql = '
            SELECT '.implode(', ', $select_fields).'
            FROM tickets
            WHERE '.implode(' AND ', $wheres)."
            $group_by WITH ROLLUP
        ";

        $counts = $db->fetchAll($sql, [$this->_ticket_ids], [Connection::PARAM_INT_ARRAY]);

        return $counts;
    }
}
