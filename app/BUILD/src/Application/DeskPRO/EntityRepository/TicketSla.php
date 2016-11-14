<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity;

class TicketSla extends AbstractEntityRepository
{
    public function getTicketSlaCountsForAgentInterface(array $slas, $filter = 'all', Entity\Person $person_context = null)
    {
        if (!$slas) {
            return [];
        }

        if (!$person_context) {
            $person_context = App::getCurrentPerson();
        }
        if (!$person_context->is_agent) {
            throw new \InvalidArgumentException('Person must be an agent');
        }
        $ids = [];
        foreach ($slas as $sla) {
            $ids[] = $sla->id;
        }

        $conn = App::getDbRead();

        // first convert sla types to ids
        $slaQueries = $queryParams = $queryTypes = [];
        $results    = $conn->fetchAll(
            'select id, sla_type from slas where id in (:ids)',
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );
        foreach ($results as $row) {
            $queryParams[$row['sla_type']][] = (int) $row['id'];
        }
        if (isset($queryParams['waiting_time'])) {
            $slaQueries[]               = '(ts.sla_id IN (:waiting_time) AND t.status = "awaiting_agent")';
            $queryTypes['waiting_time'] = Connection::PARAM_INT_ARRAY;
        }
        if (isset($queryParams['first_response'])) {
            $slaQueries[]                 = '(ts.sla_id IN (:first_response) AND t.status = "awaiting_agent")';
            $queryTypes['first_response'] = Connection::PARAM_INT_ARRAY;
        }
        if (isset($queryParams['resolution'])) {
            $slaQueries[]             = '(ts.sla_id IN (:resolution) AND t.status IN ("awaiting_agent", "awaiting_user"))';
            $queryTypes['resolution'] = Connection::PARAM_INT_ARRAY;
        }

        // convert them to queries
        // now this is the only "WHERE" condition that doesn't use index,
        // potentially can be broken apart as we do with agents/teams/participants
        $slaQueriesCombined = implode(' OR ', $slaQueries);

        $queryParams['sla_ids'] = $ids;
        $queryTypes['sla_ids']  = Connection::PARAM_INT_ARRAY;
        $pid                    = (int) $person_context['id'];
        $parts                  = [];
        if ($teams = $person_context->getHelperManager()->callName('getagentteamids', [])) {
            $teams = implode(',', $teams);
        }

        // nothing to search
        if ($filter === 'team' && !$teams) {
            return $this->formatResults($ids, []);
        }

        // builds query for a single perm condition
        $buildSlaQueryPart = function ($condition, $p = 'none') use ($slaQueriesCombined, &$parts, $pid) {
            $participantSubQuery = [
                'none'    => '',
                'include' => 'AND t.id IN (SELECT ticket_id FROM tickets_participants WHERE person_id = '.$pid.')',
                'exclude' => 'AND t.id NOT IN (SELECT ticket_id FROM tickets_participants WHERE person_id = '.$pid.')',
            ];

            $parts[] = '(
                SELECT ts.sla_id, ts.sla_status, COUNT(*) AS count
                FROM ticket_slas ts
                JOIN tickets_search_active t ON ts.ticket_id = t.id '.$participantSubQuery[$p].'
                WHERE 
                    ts.is_completed = 0
                    AND
                    ts.sla_id IN (:sla_ids)
                    AND 
                    ('.$slaQueriesCombined.')
                    AND
                    ('.$condition.')
                GROUP BY  ts.sla_id, ts.sla_status
            )';
        };

        // own tickets, excluding teams tickets and participating
        if ($filter !== 'team') {
            $q = 't.agent_id = '.$pid;
            if ($teams) {
                $q .= ' AND t.agent_team_id NOT IN ('.$teams.')';
            }
            $buildSlaQueryPart($q, 'exclude');
        }

        // own team tickets, excluding own tickets and participating
        if ($teams && $filter !== 'agent') {
            $q = 't.agent_id != '.$pid;
            $q .= ' AND t.agent_team_id IN ('.$teams.')';
            $buildSlaQueryPart($q, 'exclude');
        }

        // participating tickets, excluding own and teams
        if ($filter === 'all') {
            $q = 't.agent_id != '.$pid;
            if ($teams) {
                $q .= ' AND t.agent_team_id NOT IN ('.$teams.')';
            }
            $buildSlaQueryPart($q, 'include');
        }

        $perm           = [];
        $viewUnassigned = $person_context->hasPerm('agent_tickets.view_unassigned');
        $viewOthers     = $person_context->hasPerm('agent_tickets.view_others');
        $disallowed     = $person_context->getHelperManager()->callName('getdisalloweddepartments', []);

        if ($filter === 'all' && ($viewOthers || $viewUnassigned)) {

            // we always exclude agent and teams because they are included in previous queries
            $perm[] = 't.agent_id != '.$pid;
            if ($teams) {
                $perm[] = 't.agent_team_id NOT IN ('.$teams.')';
            }

            if ($viewUnassigned && !$viewOthers) {
                $perm[] = 't.agent_id IS NULL';
            }
            if (!$viewUnassigned && $viewOthers) {
                $perm[] = 't.agent_id IS NOT NULL';
            }

            if ($disallowed) {
                $perm[] = ' t.department_id NOT IN ('.implode(',', $disallowed).') ';
            }
        }

        if ($perm) {
            $buildSlaQueryPart(implode(' AND ', $perm), 'exclude');
        }

        $q       = implode(' UNION ALL ', $parts);
        $results = $conn->fetchAll($q, $queryParams, $queryTypes);

        return $this->formatResults($ids, $results);
    }

    protected function formatResults(array $ids, array $results)
    {
        $output = [];
        foreach ($ids as $id) {
            $output[$id] = ['ok' => 0, 'warning' => 0, 'fail' => 0];
        }
        foreach ($results as $result) {
            $output[$result['sla_id']][$result['sla_status']] += $result['count'];
        }

        return $output;
    }

    public function getTicketSlasPastThreshold($type, $limit = 250)
    {
        switch ($type) {
            case 'warning': $date_field = 'warn_date'; $statuses = "'ok'"; break;
            case 'fail': $date_field    = 'fail_date'; $statuses    = "'ok','warning'"; break;
            default: throw new \InvalidArgumentException("Unknown SLA status $type");
        }

        return $this->getEntityManager()->createQuery("
            SELECT ts
            FROM DeskPRO:TicketSla ts
            WHERE ts.is_completed = 0
                AND ts.sla_status IN ($statuses)
                AND ts.$date_field < ?0
        ")->setMaxResults($limit)->execute([new \DateTime('now', new \DateTimeZone('UTC'))]);
    }

    public function getTicketSlaAdminGraphData()
    {
        $dt = App::getCurrentPerson()->getDateTime();

        $today     = $dt->setTime(0, 0, 0)->getTimestamp();
        $yesterday = $dt->modify('-1 day')->getTimestamp();

        $dt->modify('+1 day');

        $currentDayOfWeek = $dt->format('N');
        $startAdjust      = $currentDayOfWeek - App::getCurrentPerson()->getStartOfWeek();

        if ($startAdjust) {
            if ($startAdjust > 0) {
                $dt->modify('-'.$startAdjust.' days');
            } else {
                $dt->modify('-'.(7 + $startAdjust).' days');
            }
        }

        $week_start = $dt->getTimestamp();

        $dt = App::getCurrentPerson()->getDateTime();

        $month = $dt->format('n');
        $year  = $dt->format('Y');

        $graphs = [
            'today'      => $today,
            'yesterday'  => [$yesterday, $today - 1],
            'this_week'  => $week_start,
            'this_month' => gmmktime(0, 0, 0, $month, 1, $year),
            'this_year'  => gmmktime(0, 0, 0, 1, 1, $year),
        ];

        $output = [];
        foreach ($graphs as $title => $start) {
            if (is_array($start)) {
                list($start, $end) = $start;
            } else {
                $end = null;
            }
            $data = $this->getTicketSlaStatusData($start, $end);
            if ($data) {
                $output[$title] = [
                    'ok'      => ['title' => 'OK', 'count' => 0, 'id' => 'ok', 'color' => '#abf3ae'],
                    'warning' => ['title' => 'Warning', 'count' => 0, 'id' => 'warning', 'color' => '#F7BC1F'],
                    'fail'    => ['title' => 'Failed', 'count' => 0, 'id' => 'count', 'color' => '#de5949'],
                ];
                foreach ($data as $status => $count) {
                    $output[$title][$status]['count'] = $count;
                }

                $output[$title] = array_values($output[$title]);
            }
        }

        return $output;
    }

    public function getTicketSlaStatusData($start, $end = null)
    {
        if (!$end) {
            $end = time();
        }

        return $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT ticket_slas.sla_status, COUNT(*)
            FROM ticket_slas
            INNER JOIN tickets ON (ticket_slas.ticket_id = tickets.id)
            WHERE tickets.date_created >= ? AND tickets.date_created <= ?
            GROUP BY ticket_slas.sla_status
        ', [gmdate('Y-m-d H:i:s', $start), gmdate('Y-m-d H:i:s', $end)]);
    }
}
