<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ArticleRevision;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\DownloadRevision;
use Application\DeskPRO\Entity\FeedbackRevision;
use Application\DeskPRO\Entity\NewsRevision;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\EntityRepository\TicketLog as TicketLogRepository;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use Doctrine\ORM\EntityManager;

class AgentActivity
{
    /** @var array */
    private static $ticket_log_types = [
        'changed_agent', 'participant_added', 'participant_removed',
        'ticket_created', 'ticket_split', 'merged', 'changed_category',
        'changed_brand', 'changed_department', 'changed_organization', 'changed_person',
        'message_created', 'message_removed', 'changed_priority',
        'changed_workflow', 'changed_urgency', 'changed_product',
        'changed_status', 'changed_custom_field',
    ];

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
     * @return mixed
     */
    public function getAllAgents()
    {
        return $this->em->getRepository(Person::class)->getAgents();
    }

    /**
     * @return mixed
     */
    public function getAllAgentTeams()
    {
        return $this->em->getRepository(AgentTeam::class)->getTeams();
    }

    /**
     * @param string string $agent_or_team_id
     * @param string string $date
     *
     * @return array
     */
    public function getVarsForHtmlView($agent_or_team_id = 'all', $date = '')
    {
        if ($date == '') {
            $dt = $this->person->getDateTime();
            $dt->setTime(0, 0, 0);
            $date = $dt->format('Y-m-d');
        }

        $vars = [
            'hide_unknown' => 1,
        ];
        $date       = $this->createDateFromParamString($date);
        $all_agents = $this->em->getRepository(Person::class)->getAgents();

        $agent_id = null;
        $team_id  = null;
        if (preg_match('/^team-(\d+)$/', $agent_or_team_id, $match)) {
            $team_id    = $match[1];
            $agent_list = $this->em->getRepository(AgentTeam::class)->getMembers($match[1]);
        } elseif ($agent_or_team_id && ctype_digit($agent_or_team_id)) {
            $agent_id   = $agent_or_team_id;
            $agent_list = [$this->em->getRepository(Person::class)->find($agent_or_team_id)];
        } else {
            $agent_list = false;
        }

        if (!$agent_list) {
            $agent_or_team_id = 'all';
            $agent_list       = $all_agents;
        }

        // protection against memory errors with too many agents
        // same logic as template logic in ReportsInterfaceBundle:AgentActivity:index.html.twig
        if ($agent_or_team_id === 'all' && count($agent_list) > 20) {
            $agent_or_team_id = '0';
            $agent_list       = [];
        }

        $activity = [];
        $agents   = [];

        foreach ($agent_list as $agent) {
            $logs  = [];
            $chats = $this->getChatLogForAgent($agent, $date);

            if (!empty($chats)) {
                $logs = array_merge_recursive($logs, $chats);
            }

            $ticket_logs = $this->getTicketLogForAgent($agent, $date);

            if (!empty($ticket_logs)) {
                $logs = array_merge_recursive($logs, $ticket_logs);
            }

            $revisions = $this->getRevisionsForAgent($agent, $date);

            if (!empty($revisions)) {
                $logs = array_merge_recursive($logs, $revisions);
            }

            if (!empty($logs)) {
                foreach ($logs as $hour => $by_minute) {
                    $minutely = [];

                    foreach ($by_minute as $minute => $item) {
                        $minutely[trim($minute, '_')] = $item;
                    }

                    ksort($minutely, SORT_NUMERIC);
                    $logs[$hour] = $minutely;
                }

                $agents[$agent['id']]   = $agent;
                $activity[$agent['id']] = $logs;
            }
        }

        $vars['agents']           = $agents;
        $vars['activity']         = $activity;
        $vars['agent_or_team_id'] = $agent_or_team_id;
        $vars['view_date']        = $date;
        $vars['today']            = new \DateTime('now', new \DateTimeZone('UTC'));
        $vars['agent_id']         = $agent_id;
        $vars['team_id']          = $team_id;

        if ($date->format('Y-m-d') != date('Y-m-d')) {
            $d2 = clone $date;
            $d2->add(new \DateInterval('PT24H'));
            $vars['view_next_date'] = $d2;
        }

        $d2 = clone $date;
        $d2->sub(new \DateInterval('PT24H'));
        $vars['view_prev_date'] = $d2;

        return $vars;
    }

    /**
     * @param string $agent
     * @param        $date
     *
     * @return array
     */
    protected function getChatLogForAgent($agent, $date)
    {
        $date_range = $this->createMysqlDateRangeForUser($date);
        $db         = App::getContainer()->getDb();

        // Could GROUP BY HOUR(date_created), but as timezones are in effect, it is easier to do this in PHP.
        $messages = $db->fetchAll(
            'SELECT cm.date_created, conversation_id
            FROM chat_messages AS cm
            INNER JOIN chat_conversations AS cc
            ON cc.id = conversation_id
            WHERE agent_id = ? AND cm.date_created BETWEEN ? AND ?',
            [$agent['id'], $date_range['start'], $date_range['end']]
        );

        $counts_hourly = [];

        foreach ($messages as $message) {
            $date   = $this->mysqlDateToPhpDate($message['date_created']);
            $hour   = $date->format('G');
            $minute = (int) $date->format('i');

            if (!isset($counts_hourly[$hour])) {
                $counts_hourly[$hour] = [];
            }

            if (!isset($counts_hourly[$hour][$message['conversation_id']])) {
                $counts_hourly[$hour][$message['conversation_id']] = ['count' => 1, 'last' => $minute];
            } else {
                ++$counts_hourly[$hour][$message['conversation_id']]['count'];

                if ($counts_hourly[$hour][$message['conversation_id']]['last'] < $minute) {
                    $counts_hourly[$hour][$message['conversation_id']]['last'] = $minute;
                }
            }
        }

        $counts = [];

        foreach ($counts_hourly as $hour => $stats) {
            $hour          = '_'.$hour;
            $counts[$hour] = [];

            foreach ($stats as $convo_id => $stat) {
                $convo  = $this->em->getRepository(ChatConversation::class)->find($convo_id);
                $minute = '_'.$stat['last'];

                if (!isset($counts[$hour][$minute])) {
                    $counts[$hour][$minute] = [];
                }

                $counts[$hour][$minute][] = [
                    'type'         => 'chat',
                    'count'        => $stat['count'],
                    'conversation' => $convo,
                ];
            }
        }

        return $counts;
    }

    /**
     * @param Person $agent
     * @param        $date
     *
     * @return array
     */
    private function getTicketLogForAgent(Person $agent, $date)
    {
        $counts_hourly = [];
        /** @var TicketLogRepository $ticketLogsRepo */
        $ticketLogsRepo = $this->em->getRepository(TicketLog::class);
        $logs           = $ticketLogsRepo->getLogsForAgent(
            $agent,
            ['date_range' => $this->createMysqlDateRangeForUser($date), 'types' => self::$ticket_log_types]
        );

        // collect voice tickets
        // to define ticket type
        $ticketIds = [];
        foreach ($logs as $log) {
            $ticketId             = $log->getTicket()->getId();
            $ticketIds[$ticketId] = $ticketId;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('a, m, t')
            ->from(TicketMessageVoicePhoneCall::class, 'a')
            ->join('a.message', 'm')
            ->join('m.ticket', 't')
            ->where('t.id IN (:ticket_ids)')
            ->setParameter('ticket_ids', $ticketIds)
        ;

        /** @var TicketMessageVoicePhoneCall[] $result */
        $voiceTicketIds = [];
        $result         = $qb->getQuery()->getResult();
        foreach ($result as $attribute) {
            $ticketId                  = $attribute->getMessage()->getTicket()->getId();
            $voiceTicketIds[$ticketId] = $ticketId;
        }

        $voiceTicketIds = array_values($voiceTicketIds);

        foreach ($logs as $log) {
            $date   = $this->mysqlDateToPhpDate($log['date_created']->format('Y-m-d H:i:s'));
            $hour   = $date->format('G');
            $minute = (int) $date->format('i');

            if (!isset($counts_hourly['_'.$hour])) {
                $counts_hourly['_'.$hour] = [];
            }

            if (!isset($counts_hourly['_'.$hour]['_'.$minute])) {
                $counts_hourly['_'.$hour]['_'.$minute] = [];
            }

            $counts_hourly['_'.$hour]['_'.$minute][] = [
                'type'    => 'ticket',
                'data'    => $log,
                'is_call' => in_array($log->getTicket()->getId(), $voiceTicketIds),
            ];
        }

        return $counts_hourly;
    }

    /**
     * @param string $agent
     * @param        $date
     *
     * @return array
     */
    private function getRevisionsForAgent($agent, $date)
    {
        $items = [
            'news'     => NewsRevision::class,
            'article'  => ArticleRevision::class,
            'download' => DownloadRevision::class,
            'feedback' => FeedbackRevision::class,
        ];
        $countsHourly = [];

        foreach ($items as $itemLc => $item) {
            $revisions = $this->em->getRepository($item)->getRevisionsForAgent(
                $agent,
                ['date_range' => $this->createMysqlDateRangeForUser($date)]
            );

            foreach ($revisions as $revision) {
                $dateCreated = $this->mysqlDateToPhpDate($revision['date_created']->format('Y-m-d H:i:s'));
                $hour        = $dateCreated->format('G');
                $minute      = (int) $dateCreated->format('i');

                if (!isset($countsHourly[$itemLc])) {
                    $countsHourly[$itemLc] = [];
                }

                if (!isset($countsHourly['_'.$hour]['_'.$minute])) {
                    $countsHourly['_'.$hour]['_'.$minute] = [];
                }

                $countsHourly['_'.$hour]['_'.$minute][] = ['type' => $itemLc, 'data' => $revision];
            }
        }

        return $countsHourly;
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

    /**
     * @param string $date_str
     *
     * @return \DateTime
     */
    protected function createDateFromParamString($date_str)
    {
        if (!preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $date_str)) {
            $dt = new \DateTime();
            $dt->setTimezone($this->person->getDateTimezone());
            $dt->setTime(0, 0, 0);

            return $dt;
        }

        $dt = new \DateTime();
        $dt->setTimezone($this->person->getDateTimezone());
        $dt->setTime(0, 0, 0);
        list($year, $month, $day) = explode('-', $date_str);
        $dt->setDate($year, $month, $day);
        $dt->setTime(0, 0, 0);

        return $dt;
    }

    /***
     * @param  \DateTime $date
     *
     * @return array
     */
    private function createMysqlDateRangeForUser($date)
    {
        // Let the date be reused!
        $date = clone $date;

        // Apply the user's timezone offset.
        $start_date = clone $date;
        $start_date->setTimezone(new \DateTimeZone('UTC'));

        // The timezone offset will already be applied, so no need to reapply.
        $end_date = clone $start_date;
        // Make this represent the end of the day.
        $end_date->add(new \DateInterval('P1D'));
        // Remove a single second to stop overlap, for cases where the comparison is (date >= start and date <= end).
        $end_date->sub(new \DateInterval('PT1S'));

        // Package using MySQL date format.
        $date_range = [
            'start' => $start_date->format('Y-m-d H:i:s'),
            'end'   => $end_date->format('Y-m-d H:i:s'),
        ];

        return $date_range;
    }
}
