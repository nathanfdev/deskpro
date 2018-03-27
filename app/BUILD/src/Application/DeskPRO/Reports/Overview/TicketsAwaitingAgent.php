<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;

class TicketsAwaitingAgent extends AbstractTableOverviewStat
{
    /**
     * @var GroupingField
     */
    protected $grouping_field;

    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @var array
     */
    protected $titles = null;

    public function __construct(GroupingField $grouping_field)
    {
        $this->grouping_field = $grouping_field;
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

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        $sql = "
            SELECT {$group_field['select']}, COUNT(*)
            FROM tickets AS tickets
            {$group_field['join']}
            WHERE tickets.status = 'awaiting_agent' {$group_field['where']} AND tickets.is_hold = 0
            ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '')."
            GROUP BY {$group_field['group_by']}
        ";

        $this->logger->logDebug("[TicketsAwaitingAgent] $sql");
        $this->logger->startTimer('TicketsAwaitingAgent');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketsAwaitingAgent');

        return $this->values;
    }
}
