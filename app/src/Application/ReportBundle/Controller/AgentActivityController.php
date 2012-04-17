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

class AgentActivityController extends AbstractController
{
    public function indexAction()
    {
        return $this->listAction(0, date('Y-m-d'));
    }

    public function listAction($agent_id, $date)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $vars = array();
        $date = $this->createDateFromParamString($date);

        if($agent_id) {
            $agent_list = array($em->getRepository('DeskPRO:Person')->find($agent_id));
        }
        else {
            $agent_list = $em->getRepository('DeskPRO:Person')->getAgents();
        }

        $activity = array();
        $agents = array();

        foreach($agent_list as $agent) {
            $chats = $this->getChatLogForAgent($agent, $date);
            $activity[$agent['id']] = array();

            if(!empty($chats)) {
                $agents[$agent['id']] = $agent;
                $activity[$agent['id']]['chats'] = $chats;
            }

            $ticket_logs = $this->getTicketLogForAgent($agent, $date);

            if(!empty($ticket_logs)) {
                $agents[$agent['id']] = $agent;
                $activity[$agent['id']]['tickets'] = $ticket_logs;
            }

            $revisions = $this->getRevistionsForAgent($agent, $date);

            if(!empty($revisions)) {
                $agents[$agent['id']] = $agent;
                $activity[$agent['id']] = array_merge($activity[$agent['id']], $revisions);
            }

            if(empty($activity[$agent['id']])) {
                unset($activity[$agent['id']]);
            }
        }

        $vars['agents'] = $agents;
        $vars['activity'] = $activity;
        $vars['agent_id'] = $agent_id;

        return $this->render('ReportBundle:AgentActivity:index.html.twig', $vars);
    }

    private function getRevistionsForAgent($agent, $date) {
        $items = array('News', 'Article', 'Download');
        $em = $this->getDoctrine()->getEntityManager();
        $counts_hourly = array();

        foreach($items as $item) {
            $item_lc = strtolower($item);

            $revisions = $em->getRepository('DeskPRO:'.$item.'Revision')->getRevisionsForAgent(
                $agent,
                array('date_range' => $this->createMysqlDateRangeForUser($date))
            );

            foreach($revisions as $revision) {
                $date = $this->mysqlDateToPhpDate($revision['date_created']->format('Y-m-d H:i:s'));
                $hour = $date->format('G');

                if(!isset($counts_hourly[$item_lc])) {
                    $counts_hourly[$item_lc] = array();
                }

                if(!isset($counts_hourly[$item_lc][$hour])) {
                    $counts_hourly[$item_lc][$hour] = array();
                }

                $counts_hourly[$item_lc][$hour][] = $revision;
            }
        }

        return $counts_hourly;
    }

    private function getTicketLogForAgent($agent, $date) {
        $em = $this->getDoctrine()->getEntityManager();
        $counts_hourly = array();
        $logs = $em->getRepository('DeskPRO:TicketLog')->getLogsForAgent(
            $agent,
            array('date_range' => $this->createMysqlDateRangeForUser($date))
        );

        foreach($logs as $log) {
            $date = $this->mysqlDateToPhpDate($log['date_created']->format('Y-m-d H:i:s'));
            $hour = $date->format('G');

            if(!isset($counts_hourly[$hour])) {
                $counts_hourly[$hour] = array();
            }

            $counts_hourly[$hour][] = $log;
        }

        return $counts_hourly;
    }

    private function getChatLogForAgent($agent, $date) {
        $date_range = $this->createMysqlDateRangeForUser($date);
        $db = $this->getDoctrine()->getConnection();
        // Could GROUP BY HOUR(date_created), but as timezones are in effect, it is easier to do this in PHP.
        $messages = $db->fetchAll(
            'SELECT cm.date_created, conversation_id
            FROM chat_messages AS cm
            INNER JOIN chat_conversations AS cc
            ON cc.id = conversation_id
            WHERE agent_id = ? AND cm.date_created BETWEEN ? AND ?',
            array($agent['id'], $date_range['start'], $date_range['end'])
        );

        $counts_hourly = array();

        foreach($messages as $message) {
            $date = $this->mysqlDateToPhpDate($message['date_created']);
            $hour = $date->format('G');

            if(!isset($counts_hourly[$hour])) {
                $counts_hourly[$hour] = array();
            }

            if(!isset($counts_hourly[$hour][$message['conversation_id']])) {
                $counts_hourly[$hour][$message['conversation_id']] = 1;
            }
            else {
                $counts_hourly[$hour][$message['conversation_id']]++;
            }
        }

        return $counts_hourly;
    }

    private function createDateFromParamString($date_str)
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        list($year, $month, $day) = explode('-', $date_str);
        $dt->setDate($year, $month, $day);
        $dt->setTime(0, 0, 0);

        return $dt;
    }

    private function createDateToday()
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $dt->setTime(0, 0, 0);

        return $dt;
    }

    private function createMysqlDateRangeForUser($date)
    {
        // Apply the user's timezone offset.
        $start_date = $date->setTimezone($this->person->getDateTimezone());

        // The timezone offset will already be applied, so no need to reapply.
        $end_date = clone $start_date;
        // Make this represent the end of the day.
        $end_date->add(new \DateInterval('P1D'));
        // Remove a single second to stop overlap, for cases where the comparison is (date >= start and date <= end).
        $end_date->sub(new \DateInterval('PT1S'));

        // Package using MySQL date format.
        $date_range = array(
            'start' => $start_date->format('Y-m-d H:i:s'),
            'end' => $end_date->format('Y-m-d H:i:s')
        );

        return $date_range;
    }

    private function mysqlDateToPhpDate($mysql_date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $mysql_date, new \DateTimeZone('UTC'));
        $dt->setTimeZone($this->person->getDateTimezone());
        return $dt;
    }
}