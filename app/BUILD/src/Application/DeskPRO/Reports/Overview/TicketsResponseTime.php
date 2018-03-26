<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Orb\Util\Dates;

class TicketsResponseTime extends AbstractSubgroupedTableOverviewStat
{
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

    public function __construct(GroupingField $grouping_field = null, \DateTime $date_start, \DateTime $date_end)
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

        $d1 = $this->date_start->format('Y-m-d H:i:s');
        $d2 = $this->date_end->format('Y-m-d H:i:s');

        $field = TimeTitles::makeTimeFieldSelect('tickets.total_to_first_reply');

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
                WHERE tickets.status != 'hidden' AND tickets.date_created BETWEEN '$d1' AND '$d2' AND tickets.total_to_first_reply != 0 {$group_field['where']}
                ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '')."
                GROUP BY {$group_field['group_by']}, time_group
                ORDER BY time_group ASC
            ";

            $this->logger->logDebug("[TicketsResponseTime (Grouped)] $sql");
            $this->logger->startTimer('TicketsResponseTime');
            $q = App::getDb()->executeQuery($sql, $params);
            $this->logger->logTotalTime('TicketsResponseTime');

            $this->logger->startTimer('TicketsResponseTime.collecting');

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

            $this->logger->logTotalTime('TicketsResponseTime.collecting');
        } else {
            $sql = "
                SELECT $field, COUNT(*)
                FROM tickets
                WHERE tickets.status != 'hidden' AND tickets.date_created BETWEEN '$d1' AND '$d2' AND tickets.total_to_first_reply != 0
                ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
                GROUP BY time_group
            ';

            $this->logger->logDebug("[TicketsResponseTime] $sql");
            $this->logger->startTimer('TicketsResponseTime');
            $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
            $this->logger->logTotalTime('TicketsResponseTime');
        }

        return $this->values;
    }
}
