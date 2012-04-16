<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @subpackage AdminBundle
 */

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;

class TechTimeLogController extends AbstractController
{
    /**
     * Show the list of trends. Starred trends first
     */
    public function indexAction()
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $dt->setTime(0, 0, 0);
        $vars = $this->getVarsForDate($dt);
        return $this->render('ReportBundle:TechTimeLog:index.html.twig', $vars);
    }

    public function listAction($date)
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        list($year, $month, $day) = explode('-', $date);
        $dt->setDate($year, $month, $day);
        $dt->setTime(0, 0, 0);
        $vars = $this->getVarsForDate($dt);
        return $this->render('ReportBundle:TechTimeLog:index.html.twig', $vars);
    }

    private function getVarsForDate($date)
    {
        $db = App::getDb();
        $start_date = $date->setTimezone($this->person->getDateTimezone());

        $end_date = clone $start_date;
        $end_date->add(new \DateInterval('P1D'));
        // Remove a single second to stop overlap.
        $end_date->sub(new \DateInterval('PT1S'));
        $date_range = array($start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s'));

        $agent_ids = $db->fetchAll('SELECT DISTINCT agent_id FROM agent_activity WHERE date_active BETWEEN ? AND ?', $date_range);
        $agent_repo = $this->getDoctrine()->getRepository('DeskPRO:Person');

        $block_size = 5;

        $agents = array();
        $times = array();
        $totals = array();

        foreach($agent_ids as $agent_id) {
            $agent_id = $agent_id['agent_id'];
            $agents[] = $agent_repo->find($agent_id);

            $active_times = $db->fetchAll('SELECT date_active FROM agent_activity WHERE agent_id = ? AND date_active BETWEEN ? AND ? ORDER BY date_active',
                array_merge(array($agent_id), $date_range)
            );

            $times[$agent_id] = array();

            foreach($active_times as $time) {
                $dt = $this->mysqlDateToPhpDate($time['date_active']);

                $hour = $dt->format('H');
                $minute = $dt->format('i');

                $times[$agent_id][intval(($hour * 60) / $block_size + $minute / $block_size)] = $time;
            }

            $total_minutes = count($active_times) * $block_size;
            $totals[$agent_id] = array('hours' => intval($total_minutes / 60), 'minutes' => $total_minutes % 60);
        }

        $dates_raw = $db->fetchAll('SELECT DISTINCT DATE(date_active) AS `date` FROM agent_activity ORDER BY date_active');

        foreach($dates_raw as $date_raw) {
            $new_date = new \DateTime();
            list($year, $month, $day) = explode('-', $date_raw['date']);

            if($year != 0) {
                $new_date->setDate($year, $month, $day);
                $dates[] = $new_date;
            }
        }

        $dates = array();
        $min_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MIN(date_active) FROM agent_activity'));
        $max_date = $this->mysqlDateToPhpDate($db->fetchColumn('SELECT MAX(date_active) FROM agent_activity'));

        return array(
            'agents' => $agents,
            'today' => $date,
            'times' => $times,
            'block_size' => $block_size,
            'totals' => $totals,
            'dates' => $dates,
            'max_date' => $max_date,
            'min_date' => $min_date,
        );
    }

    private function mysqlDateToPhpDate($mysql_date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $mysql_date, $this->person->getDateTimezone());
        $dt->setTimeZone(new \DateTimeZone('UTC'));
        return $dt;
    }
}