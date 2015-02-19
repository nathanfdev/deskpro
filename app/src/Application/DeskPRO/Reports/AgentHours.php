<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Dates;

class AgentHours
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    public function __construct(EntityManager $em)
    {
        $this->em     = $em;
        $this->person = App::getCurrentPerson();
    }


    /**
     * @param  string $date1
     * @param  string $date2
     * @return array
     */
    public function getVarsForHtmlView($date1, $date2 = '')
    {
        if (substr_count($date1, '-') != 2) {
            $date1 = date('Y-m-d');
        }

        list($year, $month, $day) = explode('-', $date1);

        $dt = new \DateTime();
        $dt->setTimezone($this->person->getDateTimezone());
        $dt->setDate((int)$year, (int)$month, (int)$day);
        $dt->setTime(0, 0, 0);

        $dt2 = null;
        if ($date2 && substr_count($date2, '-') == 2) {
            list($year, $month, $day) = explode('-', $date2);
            $dt2 = new \DateTime();
            $dt2->setTimezone($this->person->getDateTimezone());
            $dt2->setDate((int)$year, (int)$month, (int)$day);
            $dt2->setTime(0, 0, 0);

            if ($dt->format('Y-m-d H:i:s') == $dt2->format('Y-m-d H:i:s')) {
                return $this->getVarsForHtmlView($date1);
            } elseif ($dt2 < $dt) {
                return $this->getVarsForHtmlView($date2, $date1);
            }
        }

        $vars                = $this->getVarsForDate($dt, $dt2);
        $vars['year_start']  = $dt->format('Y');
        $vars['month_start'] = $dt->format('n');
        $vars['view_date1']  = $dt;
        $vars['day_start']   = $dt->format('j');

        $num = 1;
        if ($dt2) {
            $vars['view_date2'] = $dt2;

            $days     = array();
            $date_run = clone $dt;
            while ($date_run <= $dt2) {
                $y = $date_run->format('Y');
                $m = $date_run->format('n');
                $d = $date_run->format('j');

                if (!isset($days[$y])) {
                    $days[$y] = array();
                }
                if (!isset($days[$y][$m])) {
                    $days[$y][$m] = array();
                }

                $days[$y][$m][$d] = $d;

                $date_run->add(new \DateInterval('P1D'));
                $num++;
            }

            $vars['use_days'] = $days;
            $vars['num_days'] = $num;
        }

        return $vars;
    }


    /**
     * @param  \DateTime      $date
     * @param  null|\DateTime $end_date
     * @return array
     */
    protected function getVarsForDate($date, $end_date = null)
    {
        $db = App::getDb();

        $start_date = Dates::convertToUtcDateTime($date);

        if ($end_date) {
            $end_date = clone $end_date;
        } else {
            $end_date = clone $start_date;
            $end_date->add(new \DateInterval('P1D'));
            $end_date->sub(new \DateInterval('PT1S')); // Remove a single second to stop overlap.
        }

        $date_range = array($start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s'));

        $agent_ids  = $db->fetchAll(
            'SELECT DISTINCT agent_id FROM agent_activity WHERE date_active BETWEEN ? AND ?',
            $date_range
        );
        $agent_repo = $this->em->getRepository('DeskPRO:Person');

        $block_size = 5;

        $agents     = array();
        $times      = array();
        $times_hour = array();
        $totals     = array();

        foreach ($agent_ids as $agent_id) {
            $agent_id = $agent_id['agent_id'];
            $agents[] = $agent_repo->find($agent_id);

            $active_times = $db->fetchAll(
                'SELECT date_active FROM agent_activity WHERE agent_id = ? AND date_active BETWEEN ? AND ? ORDER BY date_active',
                array_merge(array($agent_id), $date_range)
            );

            $times[$agent_id]      = array();
            $times_hour[$agent_id] = array();

            foreach ($active_times as $time) {
                $dt = $this->mysqlDateToPhpDate($time['date_active']);

                $year   = $dt->format('Y');
                $month  = $dt->format('n');
                $day    = $dt->format('j');
                $hour   = $dt->format('G');
                $minute = $dt->format('i');

                if (!isset($times[$agent_id][$year])) {
                    $times[$agent_id][$year] = array();
                }
                if (!isset($times[$agent_id][$year][$month])) {
                    $times[$agent_id][$year][$month] = array();
                }
                if (!isset($times[$agent_id][$year][$month][$day])) {
                    $times[$agent_id][$year][$month][$day] = array();
                }

                if (!isset($times_hour[$agent_id][$year])) {
                    $times_hour[$agent_id][$year] = array();
                }
                if (!isset($times_hour[$agent_id][$year][$month])) {
                    $times_hour[$agent_id][$year][$month] = array();
                }
                if (!isset($times_hour[$agent_id][$year][$month][$day])) {
                    $times_hour[$agent_id][$year][$month][$day] = array();
                }

                $times[$agent_id][$year][$month][$day][intval(($hour * 60) / $block_size + $minute / $block_size)] = true;
                $times_hour[$agent_id][$year][$month][$day][$hour] = true;
            }

            $total_minutes     = count($active_times) * $block_size;
            $totals[$agent_id] = array('hours' => intval($total_minutes / 60), 'minutes' => $total_minutes % 60);
        }

        $min_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MIN(date_active) FROM agent_activity'));
        $max_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MAX(date_active) FROM agent_activity'));

        return array(
            'agents'     => $agents,
            'view_date'  => $date,
            'times'      => $times,
            'times_hour' => $times_hour,
            'block_size' => $block_size,
            'totals'     => $totals,
            'max_date'   => $max_date,
            'min_date'   => $min_date,
        );
    }


    /**
     * @param  string    $mysql_date
     * @return \DateTime
     */
    private function mysqlDateToPhpDate($mysql_date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $mysql_date, new \DateTimeZone('UTC'));
        $dt->setTimeZone($this->person->getDateTimezone());

        return $dt;
    }
}
