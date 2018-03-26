<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Orb\Util\Dates;

class TicketsResolved extends AbstractTableOverviewStat
{
    /**
     * @var GroupingField
     */
    protected $grouping_field;

    /**
     * @var \DateTime
     */
    protected $date_start;

    /**
     * @var \DateTime
     */
    protected $date_end;

    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @var array
     */
    protected $titles = null;

    public function __construct(GroupingField $grouping_field, \DateTime $date_start, \DateTime $date_end)
    {
        $this->grouping_field = $grouping_field;
        $this->date_start     = Dates::convertToUtcDateTime($date_start);
        $this->date_end       = Dates::convertToUtcDateTime($date_end);
    }

    /**
     * @return string[]
     */
    public function getTitles()
    {
        return $this->grouping_field->getTitles($this->getValues());
    }

    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $group_field = $this->grouping_field->getFieldInfo();

        $d1 = $this->date_start->format('Y-m-d H:i:s');
        $d2 = $this->date_end->format('Y-m-d H:i:s');

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        $sql = "
            SELECT {$group_field['select']}, COUNT(*)
            FROM tickets
            {$group_field['join']}
            WHERE tickets.status = 'resolved' AND tickets.date_resolved BETWEEN '$d1' AND '$d2' {$group_field['where']}
            ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '')."
            GROUP BY {$group_field['group_by']}
        ";

        $this->logger->logDebug("[TicketsResolved] $sql");
        $this->logger->startTimer('TicketsResolved');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketsResolved');

        return $this->values;
    }
}
