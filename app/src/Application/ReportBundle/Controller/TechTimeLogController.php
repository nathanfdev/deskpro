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
        $vars = $this->getVarsForDate(new \DateTime());
        return $this->render('ReportBundle:TechTimeLog:index.html.twig', $vars);
    }

    public function listAction($date)
    {
        $dt = new \DateTime();
        list($year, $month, $day) = explode('-', $date);
        $dt->setDate($year, $month, $day);
        $vars = $this->getVarsForDate($dt);
        return $this->render('ReportBundle:TechTimeLog:index.html.twig', $vars);
    }

    private function getVarsForDate($date)
    {
        $db = App::getDb();
        $agent_ids = $db->fetchAll('SELECT DISTINCT agent_id FROM agent_activity WHERE DATE(date_active) = ?', array($date->format('Y-m-d')));
        $agent_repo = $this->getDoctrine()->getRepository('DeskPRO:Person');
        $block_size = 5;

        $agents = array();
        $times = array();
        $totals = array();

        foreach($agent_ids as $agent_id) {
            $agent_id = $agent_id['agent_id'];
            $agents[] = $agent_repo->find($agent_id);
            $active_times = $db->fetchAll('SELECT HOUR(date_active) AS `hour`, MINUTE(date_active) AS `minute` FROM agent_activity WHERE DATE(date_active) = ? AND agent_id = ?', array($date->format('Y-m-d'), $agent_id));

            $times[$agent_id] = array();

            foreach($active_times as $time) {
                $minute = $time['minute'];
                $hour = $time['hour'];
                $times[$agent_id][intval(($hour * 60) / $block_size + $minute / $block_size)] = $time;
            }

            $total_minutes = count($active_times) * $block_size;
            $totals[$agent_id] = array('hours' => intval($total_minutes / 60), 'minutes' => $total_minutes % 60);
        }

        $dates_raw = $db->fetchAll('SELECT DISTINCT DATE(date_active) AS `date` FROM agent_activity ORDER BY date_active');
        $dates = array();

        foreach($dates_raw as $date_raw) {
            $new_date = new \DateTime();
            list($year, $month, $day) = explode('-', $date_raw['date']);

            if($year != 0) {
                $new_date->setDate($year, $month, $day);
                $dates[] = $new_date;
            }
        }

        return array(
            'agents' => $agents,
            'today' => $date,
            'times' => $times,
            'block_size' => $block_size,
            'totals' => $totals,
            'dates' => $dates,
        );
    }
}