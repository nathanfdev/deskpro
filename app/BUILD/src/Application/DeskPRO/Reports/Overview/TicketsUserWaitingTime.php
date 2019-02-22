<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;

class TicketsUserWaitingTime extends AbstractSubgroupedTableOverviewStat
{
    /**
     * @var int[]
     */
    protected $values = null;

    /**
     * @var array
     */
    protected $titles = null;

    public function __construct(GroupingField $grouping_field = null)
    {
        $this->grouping_field = $grouping_field;
    }

    /**
     * @return string[]
     */
    public function getTitles()
    {
        $largest = 0;
        foreach ($this->getValues() as $time => $x) {
            if ($time > $largest) {
                $largest = $time;
            }
        }

        $titles = [];

        foreach (TimeTitles::$time_phrases as $time => $phrase) {
            if ($time > $largest) {
                break;
            }

            $titles[$time] = $phrase;
        }

        return $titles;
    }

    /**
     * @return string[]
     */
    public function getSubgroupTitles()
    {
        if (!$this->grouping_field) {
            return;
        }

        $collect = [];
        foreach ($this->getValues() as $sub_groups) {
            foreach ($sub_groups as $group_id => $count) {
                $collect[$group_id] = $group_id;
            }
        }

        return $this->grouping_field->getTitles($collect);
    }

    /**
     * @return int[]
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $now   = time();
        $field = TimeTitles::makeTimeFieldSelect("($now - UNIX_TIMESTAMP(tickets.date_user_waiting))");

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        if ($this->grouping_field) {
            $group_field = $this->grouping_field->getFieldInfo();
            $sql         = "
                SELECT {$group_field['select']}, $field, COUNT(*)
                FROM tickets
                {$group_field['join']}
                WHERE tickets.status IN ('awaiting_agent') {$group_field['where']} 
                ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '')."
                GROUP BY {$group_field['group_by']}, time_group
                ORDER BY time_group ASC
            ";

            $this->logger->logDebug("[TicketsUserWaitingTime (Grouepd)] $sql");
            $this->logger->startTimer('TicketsUserWaitingTime');
            $q = App::getDb()->executeQuery($sql, $params);
            $this->logger->logTotalTime('TicketsUserWaitingTime');

            $this->logger->startTimer('TicketsUserWaitingTime.collecting');

            $this->values = [];
            while ($row = $q->fetch(\PDO::FETCH_NUM)) {
                $group_id   = $row[0];
                $time_group = $row[1];
                $count      = $row[2];
                if (!isset($this->values[$time_group])) {
                    $this->values[$time_group] = [];
                }

                if (!isset($this->values[$time_group][$group_id])) {
                    $this->values[$time_group][$group_id] = 0;
                }

                $this->values[$time_group][$group_id] += $count;
            }

            $this->logger->logTotalTime('TicketsUserWaitingTime.collecting');
        } else {
            $sql = "
                SELECT $field, COUNT(*)
                FROM tickets
                WHERE tickets.status IN ('awaiting_agent') 
                ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
                GROUP BY time_group
            ';

            $this->logger->logDebug("[TicketsUserWaitingTime] $sql");
            $this->logger->startTimer('TicketsUserWaitingTime');
            $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
            $this->logger->logTotalTime('TicketsUserWaitingTime');
        }

        return $this->values;
    }
}
