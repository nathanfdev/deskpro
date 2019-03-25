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
            'pending'        => 'Pending (On Hold)',
        ];

        return $s;
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
            FROM tickets AS tickets WHERE status != "pending" 
            '.($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
            GROUP BY tickets.status
        ';

        $this->logger->logDebug("[TicketsStatus] $sql");
        $this->logger->startTimer('TicketsStatus');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketsStatus');

        $sql = "
            SELECT tickets.status, COUNT(*)
            FROM tickets AS tickets WHERE status = 'pending' 
            GROUP BY tickets.status
        ";

        $this->logger->logDebug("[TicketsStatus w hold] $sql");
        $this->logger->startTimer('TicketsStatus_w_hold');
        $this->values = array_merge($this->values, App::getDb()->fetchAllKeyValue($sql, $params));
        $this->logger->logTotalTime('TicketsStatus_w_hold');

        return $this->values;
    }
}
