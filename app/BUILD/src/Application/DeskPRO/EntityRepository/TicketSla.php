<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Application\DeskPRO\Entity;
use Application\DeskPRO\Searcher\TicketSearch;
use Doctrine\DBAL\Connection;

class TicketSla extends AbstractEntityRepository
{
    /**
     * @param Entity\Sla[]  $slas
     * @param mixed         $filter
     * @param Entity\Person $person_context
     *
     * @return array
     */
    public function getCachedTicketSlaCountsForAgentInterface(array $slas, $filter, Entity\Person $person_context)
    {
        if (!count($slas)) {
            return [];
        }

        if (!App::$container->getSetting('enable_cached_sla_counts')) {
            return $this->getTicketSlaCountsForAgentInterface($slas, $filter, $person_context);
        }

        /** @var \Application\DeskPRO\DBAL\Connection $db */
        $db = $this->_em->getConnection();

        $values = $db->fetchAllKeyed("
            SELECT name, value_array, date_expire
            FROM people_prefs
            WHERE person_id = ? AND name LIKE 'ticket_sla_counts.%'
        ", [$person_context->getId()], 'name');

        $results  = [];
        $calcSlas = [];

        $currentTime = new \DateTime();

        foreach ($slas as $sla) {
            $cacheId = 'ticket_sla_counts.'.$sla->getId();
            $value   = isset($values[$cacheId]) ? $values[$cacheId] : null;
            $expire  = new \DateTime($value['date_expire']);

            if ($value && $currentTime < $expire) {
                $r = @unserialize($value['value_array']);
            } else {
                $r = null;
            }

            if ($r) {
                $results[$sla->getId()] = $r;
            } else {
                $calcSlas[] = $sla;
            }
        }

        if ($calcSlas) {
            $calcResults = $this->getTicketSlaCountsForAgentInterface($calcSlas, $filter, $person_context);
            $inserts     = [];
            $expire      = date('Y-m-d H:i:s', time() + 900);

            if ($calcResults) {
                foreach ($calcResults as $slaId => $slaRes) {
                    $results[$slaId] = $slaRes;
                }

                foreach ($calcResults as $slaId => $calcRes) {
                    $inserts[] = [
                        'person_id'   => $person_context->getId(),
                        'name'        => "ticket_sla_counts.{$slaId}",
                        'value_str'   => null,
                        'value_array' => serialize($calcRes),
                        'date_expire' => $expire,
                    ];
                }

                if ($inserts) {
                    $prefNames = array_map(function ($insert) {
                        return $insert['name'];
                    }, $inserts);

                    $db->executeUpdate(
                        'DELETE FROM people_prefs WHERE person_id = :person_id AND name IN(:name)',
                        [
                            'person_id' => $person_context->getId(),
                            'name'      => $prefNames,
                        ],
                        [
                            'person_id' => \PDO::PARAM_INT,
                            'name'      => Connection::PARAM_STR_ARRAY,
                        ]
                    );
                    $db->batchInsert('people_prefs', $inserts, true);
                }
            }
        }

        return $results;
    }

    public function getTicketSlaCountsForAgentInterface(array $slas, $filter, Entity\Person $person_context)
    {
        $s = new TicketSearch();
        if ($person_context) {
            $s->setPersonContext($person_context);
        }
        $s->setOrderByCode('ticket.id:desc');
        $s->addRawSelect('ts.sla_id, ts.sla_status');
        $s->addRawJoin('INNER JOIN ticket_slas AS ts ON (ts.ticket_id = tickets.id)');

        $slaQueries = $queryParams = $queryTypes = [];

        $ids = [];
        foreach ($slas as $sla) {
            $id    = (int) $sla->id;
            $ids[] = $id;
            // first map sla types to ids
            $queryParams[$sla->sla_type][] = $id;
        }

        if (isset($queryParams['waiting_time'])) {
            $slaQueries[] = '(ts.sla_id IN ('.implode(',', $queryParams['waiting_time']).') AND tickets.status = "awaiting_agent")';
        }
        if (isset($queryParams['first_response'])) {
            $slaQueries[] = '(ts.sla_id IN ('.implode(',', $queryParams['first_response']).') AND tickets.status = "awaiting_agent")';
        }
        if (isset($queryParams['resolution'])) {
            $slaQueries[] = '(ts.sla_id IN ('.implode(',', $queryParams['resolution']).') AND tickets.status IN ("awaiting_agent", "awaiting_user"))';
        }

        $where = '
            ts.is_completed = 0
            AND
            ts.sla_id IN ('.implode(',', $ids).')
            AND 
            ('.implode(' OR ', $slaQueries).')
        ';

        $s->addRawWhere($where);
        $s->addRawWhere("tickets.status IN ('awaiting_user', 'awaiting_agent')");

        switch ($filter) {
            case 'agent':
                if (!$person_context) {
                    return $this->formatResults($ids, []);
                }
                $s->addTerm(TicketSearch::TERM_AGENT, TicketSearch::OP_IS, $person_context->getId());
                break;

            case 'team':
                if (!$person_context) {
                    return $this->formatResults($ids, []);
                }
                $teams = $person_context->getHelperManager()->callName('getagentteamids', []);
                if (!$teams) {
                    return $this->formatResults($ids, []);
                }
                $s->addTerm(TicketSearch::TERM_AGENT_TEAM, TicketSearch::OP_IS, $teams);
                break;
        }

        $sql = 'SELECT sla_id, sla_status, COUNT(*) AS count FROM ('.$s->getSql().') AS r GROUP BY sla_id, sla_status';

        $conn    = App::getDbRead('search.filter.tickets');
        $results = $conn->fetchAll($sql);

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
