<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Dates;

class AgentHours
{
    /**
     * @var \Doctrine\ORM\EntityManager
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
     * @param string $date1
     * @param string $date2
     *
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
        $dt->setDate((int) $year, (int) $month, (int) $day);
        $dt->setTime(0, 0, 0);

        $dt2 = null;
        if ($date2 && substr_count($date2, '-') == 2) {
            list($year, $month, $day) = explode('-', $date2);
            $dt2                      = new \DateTime();
            $dt2->setTimezone($this->person->getDateTimezone());
            $dt2->setDate((int) $year, (int) $month, (int) $day);
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

            $days     = [];
            $date_run = clone $dt;
            while ($date_run <= $dt2) {
                $y = $date_run->format('Y');
                $m = $date_run->format('n');
                $d = $date_run->format('j');

                if (!isset($days[$y])) {
                    $days[$y] = [];
                }
                if (!isset($days[$y][$m])) {
                    $days[$y][$m] = [];
                }

                $days[$y][$m][$d] = $d;

                $date_run->add(new \DateInterval('P1D'));
                ++$num;
            }

            $vars['use_days'] = $days;
            $vars['num_days'] = $num;
        }

        return $vars;
    }

    /**
     * @param \DateTime      $date
     * @param null|\DateTime $end_date
     *
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

        $date_range = [$start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s')];

        $agent_ids = $db->fetchAll(
            'SELECT DISTINCT agent_id FROM agent_activity WHERE date_active BETWEEN ? AND ?',
            $date_range
        );
        $agent_repo = $this->em->getRepository('DeskPRO:Person');

        $block_size = 5;

        $agents     = [];
        $times      = [];
        $times_hour = [];
        $totals     = [];

        foreach ($agent_ids as $agent_id) {
            $agent_id = $agent_id['agent_id'];
            $agents[] = $agent_repo->find($agent_id);

            $active_times = $db->fetchAll(
                'SELECT date_active FROM agent_activity WHERE agent_id = ? AND date_active BETWEEN ? AND ? ORDER BY date_active',
                array_merge([$agent_id], $date_range)
            );

            $times[$agent_id]      = [];
            $times_hour[$agent_id] = [];

            foreach ($active_times as $time) {
                $dt = $this->mysqlDateToPhpDate($time['date_active']);

                $year   = $dt->format('Y');
                $month  = $dt->format('n');
                $day    = $dt->format('j');
                $hour   = $dt->format('G');
                $minute = $dt->format('i');

                if (!isset($times[$agent_id][$year])) {
                    $times[$agent_id][$year] = [];
                }
                if (!isset($times[$agent_id][$year][$month])) {
                    $times[$agent_id][$year][$month] = [];
                }
                if (!isset($times[$agent_id][$year][$month][$day])) {
                    $times[$agent_id][$year][$month][$day] = [];
                }

                if (!isset($times_hour[$agent_id][$year])) {
                    $times_hour[$agent_id][$year] = [];
                }
                if (!isset($times_hour[$agent_id][$year][$month])) {
                    $times_hour[$agent_id][$year][$month] = [];
                }
                if (!isset($times_hour[$agent_id][$year][$month][$day])) {
                    $times_hour[$agent_id][$year][$month][$day] = [];
                }

                $times[$agent_id][$year][$month][$day][intval(($hour * 60) / $block_size + $minute / $block_size)] = true;
                $times_hour[$agent_id][$year][$month][$day][$hour]                                                 = true;
            }

            $total_minutes     = count($active_times) * $block_size;
            $totals[$agent_id] = ['hours' => intval($total_minutes / 60), 'minutes' => $total_minutes % 60];
        }

        $min_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MIN(date_active) FROM agent_activity'));
        $max_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MAX(date_active) FROM agent_activity'));

        return [
            'agents'     => $agents,
            'view_date'  => $date,
            'times'      => $times,
            'times_hour' => $times_hour,
            'block_size' => $block_size,
            'totals'     => $totals,
            'max_date'   => $max_date,
            'min_date'   => $min_date,
        ];
    }

    /**
     * @param string $mysql_date
     *
     * @return \DateTime
     */
    private function mysqlDateToPhpDate($mysql_date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $mysql_date, new \DateTimeZone('UTC'));
        $dt->setTimeZone($this->person->getDateTimezone());

        return $dt;
    }
}
