<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;
use Orb\Util\Dates;
use Orb\Util\Numbers;

class TicketsOpenedHour extends AbstractTableOverviewStat implements PersonContextInterface
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

    /**
     * @var string
     */
    protected $date_group;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    public function __construct($date_group, \DateTime $date_start, \DateTime $date_end)
    {
        $this->date_group = $date_group;
        $this->date_start = $date_start;
        $this->date_end   = $date_end;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array|mixed
     */
    public function getTitles()
    {
        switch ($this->date_group) {
            case 'hour':
                $titles      = array_combine(range(1, 23), range(1, 23));
                $titles['0'] = 0;

                foreach ($titles as &$x) {
                    if ($x == 0) {
                        $x = '12am';
                    } elseif ($x == 12) {
                        $x = '12pm';
                    } elseif ($x < 12) {
                        $x = $x.'am';
                    } else {
                        $x = ($x - 12).'pm';
                    }
                }

                break;

            case 'weekday':
                // Sunday is start of week in MySQL, hence weird indexes
                $titles = [
                    0 => 'Monday',
                    1 => 'Tuesday',
                    2 => 'Wednesday',
                    3 => 'Thursday',
                    4 => 'Friday',
                    5 => 'Saturday',
                    6 => 'Sunday',
                ];
                break;

            case 'day':
                $days   = Dates::daysInMonth($this->date_start->format('n'), $this->date_start->format('Y'));
                $titles = [];
                foreach (range(1, $days) as $d) {
                    $titles[$d] = $d.Numbers::ordinalSuffix($d);
                }

                break;

            case 'month':
                $titles = [
                    1  => 'Jan',
                    2  => 'Feb',
                    3  => 'Mar',
                    4  => 'Apr',
                    5  => 'May',
                    6  => 'Jun',
                    7  => 'Jul',
                    8  => 'Aug',
                    9  => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec',
                ];
                break;

            default:
                throw new \InvalidArgumentException("Unknown date group: {$this->date_group}");
        }

        return $titles;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array|\int[]|mixed|null
     */
    public function getValues()
    {
        if ($this->values !== null) {
            return $this->values;
        }

        // Convert input datetime which has timezone data, into UTC for db range
        $date1 = Dates::convertToUtcDateTime($this->date_start);
        $date2 = Dates::convertToUtcDateTime($this->date_end);

        $d1 = $date1->format('Y-m-d H:i:s');
        $d2 = $date2->format('Y-m-d H:i:s');

        // Get offset of original date from UTC, we need for mysql
        $offset = 0;
        if ($this->person_context) {
            $offset = $this->person_context->getTimezoneOffsetSeconds();
        }

        switch ($this->date_group) {
            case 'hour':
                $date_group = "HOUR(DATE_ADD(tickets.date_created, INTERVAL $offset SECOND))";
                break;

            case 'weekday':
                $date_group = "WEEKDAY(DATE_ADD(tickets.date_created, INTERVAL $offset SECOND))";
                break;

            case 'day':
                $date_group = "DAYOFMONTH(DATE_ADD(tickets.date_created, INTERVAL $offset SECOND))";
                break;

            case 'month':
                $date_group = "MONTH(DATE_ADD(tickets.date_created, INTERVAL $offset SECOND))";
                break;

            default:
                throw new \InvalidArgumentException("Unknown date group: {$this->date_group}");
        }

        $params = [];
        if ($this->agentTeam) {
            $params['team_id'] = $this->agentTeam;
        }

        $sql = "
            SELECT $date_group AS date_group, COUNT(*)
            FROM tickets
            WHERE tickets.status != 'hidden' AND tickets.date_created BETWEEN '$d1' AND '$d2'
            ".($this->agentTeam ? ' AND agent_team_id = :team_id ' : '').'
            GROUP BY date_group
        ';

        $this->logger->logDebug("[TicketsOpenedHour] $sql");
        $this->logger->startTimer('TicketsOpenedHour');
        $this->values = App::getDb()->fetchAllKeyValue($sql, $params);
        $this->logger->logTotalTime('TicketsOpenedHour');

        return $this->values;
    }
}
