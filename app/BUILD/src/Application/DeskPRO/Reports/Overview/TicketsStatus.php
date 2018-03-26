<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;

class TicketsStatus extends AbstractTableOverviewStat
{
    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @return string[]
     */
    public function getTitles()
    {
        $s = [
            'awaiting_agent' => 'Awaiting Agent',
            'awaiting_user'  => 'Awaiting User',
            'resolved'       => 'Resolved',
            'archived'       => 'Archived',
            'hidden'         => 'Hidden',
        ];

        $return = [];
        foreach ($s as $k => $v) {
            $return[$k]         = $v;
            $return[$k.'_hold'] = $v.' (On Hold)';
        }

        return $return;
    }

    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        $sql = '
            SELECT tickets.status, COUNT(*)
            FROM tickets AS tickets WHERE is_hold = 0
            '.($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
            GROUP BY tickets.status
        ';

        $this->logger->logDebug("[TicketsStatus] $sql");
        $this->logger->startTimer('TicketsStatus');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketsStatus');

        $sql = "
            SELECT CONCAT(tickets.status, '_hold'), COUNT(*)
            FROM tickets AS tickets WHERE is_hold = 1
            GROUP BY tickets.status
        ";

        $this->logger->logDebug("[TicketsStatus w hold] $sql");
        $this->logger->startTimer('TicketsStatus_w_hold');
        $this->values = array_merge($this->values, App::getDb()->fetchAllKeyValue($sql, $params));
        $this->logger->logTotalTime('TicketsStatus_w_hold');

        return $this->values;
    }
}
