<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\ChatConversation as ChatConversationEntity;
use Application\DeskPRO\Entity\CustomFieldData as CustomFieldDataEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketDeleted as TicketDeletedEntity;
use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use Doctrine\ORM\Query\Expr\Join;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class Ticket extends AbstractEntityRepository
{
    public function saveNewMessage(Entity\Ticket $ticket, Entity\TicketMessage $message)
    {
        $ticket->addMessage($message);

        $this->_em->persist($message);
        $this->_em->flush();
    }

    public function saveNewTicket(
        Entity\Ticket $ticket,
        Entity\TicketMessage $message,
        Entity\Person $person,
        Entity\Brand $brand
    ) {
        $ticket->addMessage($message);
        $ticket->setPerson($person);
        $ticket->setBrand($brand);

        $this->_em->persist($ticket);
        $this->_em->persist($message);
        $this->_em->persist($person);
        $this->_em->flush();
    }

    /**
     * The ticket is.
     *
     * @param PersonEntity $person
     * @param \DateTime    $date_last_reply
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return TicketEntity
     */
    public function findMostRecentSmsTicketFromPerson(PersonEntity $person, $date_last_reply = null)
    {
        if (!$date_last_reply) {
            $date_last_reply = new \DateTime('now - 3 days');
        }

        $query = $this->getEntityManager()->createQuery(
            '
                SELECT t
                FROM DeskPRO:Ticket t
                WHERE t.person = :person
                AND t.creation_system = :creation_system
                AND (t.date_last_user_reply > :date_last_reply OR t.date_last_agent_reply > :date_last_reply)
                ORDER BY t.date_last_user_reply DESC
            '
            )
            ->setMaxResults(1)
            ->setParameter('date_last_reply', $date_last_reply)
            ->setParameter('person', $person->getId())
            ->setParameter('creation_system', IncomingSmsProcessor::TICKET_CREATION_SYSTEM);

        $ticket = $query->getOneOrNullResult();

        return $ticket;
    }

    /**
     * Find a ticket by its TAC.
     *
     * @param $access_code
     *
     * @return mixed|null|object|void
     */
    public function getByAccessCode($access_code)
    {
        $info = Entity\Ticket::decodeAccessCode($access_code);
        if (!$info) {
            return;
        }

        $rec = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.id = :ticket_id AND t.auth = :auth
        ')->setParameters($info)->setMaxResults(1)->getOneOrNullResult();

        if (!$rec) {
            // Try to find it through merges
            $del_ticket = $this->getEntityManager()->createQuery('
                SELECT t
                FROM DeskPRO:TicketDeleted t
                WHERE t.ticket_id = :ticket_id AND t.old_ptac = :auth
            ')->setParameters($info)->setMaxResults(1)->getOneOrNullResult();

            if ($del_ticket) {
                $rec = $this->resolveDeletedTicket($del_ticket);
            }
        }

        return $rec;
    }

    /**
     * Find a ticket ID and if it cant be found, try to find it through deleted records.
     *
     * @param $ticket_id
     *
     * @return TicketEntity
     */
    public function findTicketId($ticket_id)
    {
        $ticket = $this->_em->find('DeskPRO:Ticket', $ticket_id);
        if ($ticket) {
            return $ticket;
        }

        $del_ticket = $this->findDeletedTicket($ticket_id);

        if (!$del_ticket) {
            return;
        }

        return $this->resolveDeletedTicket($del_ticket);
    }

    /**
     * Find a ticket ref and if it cant be found, try to find it through deleted records.
     *
     * @param string $ticket_ref
     *
     * @return TicketEntity
     */
    public function findTicketRef($ticket_ref)
    {
        /** @var TicketEntity $ticket */
        $ticket = $this->findOneBy(['ref' => $ticket_ref]);
        if ($ticket) {
            return $ticket;
        }

        $del_ticket = $this->_em->createQuery('
            SELECT t
            FROM DeskPRO:TicketDeleted t
            WHERE t.old_ref = ?0
        ')->setParameters([$ticket_ref])->setMaxResults(1)->getOneOrNullResult();

        if (!$del_ticket) {
            return;
        }

        return $this->resolveDeletedTicket($del_ticket);
    }

    /**
     * @param string $ref
     *
     * @return TicketEntity[]
     */
    public function searchTicketRef($ref)
    {
        $ref     = str_replace(['%', '_'], ['\\%', '\\_'], $ref);
        $tickets = $this->_em->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.ref LIKE ?0
            ORDER BY t.id DESC
        ')->setParameters([$ref.'%'])->setMaxResults(10)->execute();

        $new_ticket_ids = $this->getEntityManager()->getConnection()->fetchAll('
            SELECT new_ticket_id
            FROM tickets_deleted
            WHERE old_ref LIKE ?
        ', [$ref]);
        if ($new_ticket_ids) {
            $other_tickets = $this->getByIds($new_ticket_ids);
            if (count($other_tickets)) {
                $ret = [];
                foreach ($tickets as $t) {
                    $ret[$t->getId()] = $t;
                }
                foreach ($other_tickets as $t) {
                    $ret[$t->getId()] = $t;
                }
                $tickets = array_values($ret);
            }
        }

        return $tickets;
    }

    /**
     * @param \Application\DeskPRO\Entity\TicketDeleted $del_ticket
     *
     * @return null|object|void
     */
    public function resolveDeletedTicket(TicketDeletedEntity $del_ticket)
    {
        $new_delticket = $del_ticket;
        while ($new_delticket) {
            $del_ticket    = $new_delticket;
            $new_delticket = $this->findDeletedTicket($del_ticket->new_ticket_id);
        }

        if (!$del_ticket) {
            return;
        }
        $ticket = $this->find($del_ticket->new_ticket_id);

        return $ticket;
    }

    /**
     * @param int|string $ticket_id
     *
     * @return null|TicketDeletedEntity
     */
    public function resolveLastDeletedTicket($ticket_id)
    {
        $del_ticket = $this->findDeletedTicket($ticket_id);

        if (!$del_ticket) {
            return null;
        }

        do {
            $next_del_ticket = $this->findDeletedTicket($del_ticket->new_ticket_id);
            $del_ticket      = $next_del_ticket ?: $del_ticket;
        } while ($next_del_ticket);

        return $del_ticket;
    }

    /**
     * @param int|string $ticket_id
     *
     * @return null|TicketDeletedEntity
     */
    public function findDeletedTicket($ticket_id)
    {
        return $this->_em->createQuery(
            '
            SELECT t
            FROM DeskPRO:TicketDeleted t
            WHERE t.ticket_id = ?0
        '
        )
            ->setParameters([$ticket_id])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * Get tickets by specific ids.
     *
     * @param array $ids
     *
     * @return array
     */
    public function getTicketsFromIds(array $ids)
    {
        // Only valid ID's please :)
        // Do this because Doctrine doesnt have proper IN()
        // escaping until 2.1
        $ids = array_filter($ids, function ($val) {
            if (Numbers::isInteger($val)) {
                return true;
            }

            return false;
        });

        if (!$ids) {
            return [];
        }

        $tickets = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.id IN(?0)
            ORDER BY t.id ASC
        ')->execute([$ids]);

        return $tickets;
    }

    /**
     * Fetches full ticket object graphs for ticket listing results.
     *
     * @param array $ids
     *
     * @return array|mixed
     */
    public function getTicketsResultsFromIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        // Must be numerically indexed
        $ids = array_values($ids);

        $tickets = $this->getEntityManager()->createQuery('
            SELECT t, slas
            FROM DeskPRO:Ticket t INDEX BY t.id
            LEFT JOIN t.ticket_slas slas
            WHERE t.id IN(?1)
            ORDER BY t.id ASC
        ')->setParameter(1, $ids)->execute();

        return $tickets;
    }

    /**
     * Get all tickets a person owns, or is a participant in.
     * This is usually used to fetch a list of tickets for an end-user.
     *
     * @param PersonEntity $person
     * @param PersonEntity $agent
     * @param null         $limit
     * @param null         $sort_by
     * @param string       $sort_order
     *
     * @return array
     */
    public function getPersonTickets(
        Entity\Person $person,
        Entity\Person $agent,
        $limit = null,
        $sort_by = null,
        $sort_order = 'DESC',
        $departmentIds = []
    ) {
        $agentWherePermissions = 'tickets.agent_id = ?';
        $wheres                = [];
        $join                  = 'INNER JOIN tickets ON tickets.id = tickets_participants.ticket_id';

        if (!$agent->hasPerm('agent_tickets.view_unassigned')) {
            $wheres[] = '(tickets.agent_id IS NOT NULL OR tickets.agent_team_id IS NOT NULL)';
        }
        if (!$agent->hasPerm('agent_tickets.view_others')) {
            $wheres[] = 'tickets.agent_id IS NULL';
            $wheres[] = 'tickets.agent_team_id IS NULL';
        }

        if (!$person->isAgent()) {
            $params      = [$person->getId(), $agent->getId()];
            $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT];

            if ($departmentIds) {
                $wheres[] = 'tickets.department_id IN (?)';

                $params = [
                    $person->getId(),
                    $agent->getId(),
                    $departmentIds,
                ];
                $paramsTypes = [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    Connection::PARAM_INT_ARRAY,
                ];
            }

            if ($wheres) {
                $where = sprintf(' AND (%s OR (%s))', $agentWherePermissions, implode(' AND ', $wheres));
            } else {
                $where = sprintf(' AND %s', $agentWherePermissions);
            }

            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT DISTINCT id FROM (
                    SELECT tickets.id FROM tickets WHERE tickets.person_id = ? $where
                    UNION
                    SELECT tickets_participants.ticket_id FROM tickets_participants $join WHERE tickets_participants.person_id = ? $where
                    LIMIT 2000
                ) AS t
            ", array_merge($params, $params), array_merge($paramsTypes, $paramsTypes)); // double params, cause there are two parts of query
        } else {
            $params      = [$person->getId(), $agent->getId()];
            $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT];
            if ($departmentIds) {
                $wheres[]    = 'tickets.department_id IN (?)';
                $params      = [$person->getId(), $agent->getId(), $departmentIds];
                $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY];
            }
            if ($wheres) {
                $where = sprintf('AND (%s OR (%s))', $agentWherePermissions, implode(' AND ', $wheres));
            } else {
                $where = sprintf('AND %s', $agentWherePermissions);
            }

            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id FROM tickets WHERE person_id = ? $where
                LIMIT 2000
            ", $params, $paramsTypes);
        }

        if (!$ids) {
            return [];
        }

        if ($sort_by === 'last_reply') {
            $ids = $this->getEntityManager()->getConnection()->fetchAllCol(
                '
                                SELECT id
                                FROM tickets
                                WHERE id IN (?)
                ORDER BY GREATEST(
					COALESCE(date_last_user_reply,0),
					COALESCE(date_last_agent_reply,0)
				) DESC
            ',
            [$ids], [Connection::PARAM_INT_ARRAY]);
        } elseif ($sort_by == 'status') {
            $ids = App::getDb()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE id IN (?)
                ORDER BY FIELD(tickets.status, 'awaiting_agent', 'awaiting_user', 'resolved', 'archived', 'hidden') ASC, IF(tickets.status = 'awaiting_agent', tickets.urgency, 0) DESC, tickets.id DESC
            ", [$ids], [Connection::PARAM_INT_ARRAY]);
        } elseif ($sort_by && in_array(strtolower($sort_by), $this->_em->getClassMetadata('DeskPRO:Ticket')->getFieldNames())) {
            $sort_by = strtolower($sort_by);

            $sort_order = strtolower($sort_order);
            $sort_order = in_array($sort_order, ['asc', 'desc']) ? $sort_order : 'DESC';

            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE id IN (?)
                ORDER BY $sort_by $sort_order
            ", [$ids], [Connection::PARAM_INT_ARRAY]
            );
        } else {
            sort($ids, \SORT_NUMERIC);
        }

        if ($limit && count($ids) > $limit) {
            $ids = array_slice($ids, 0, $limit);
        }

        return $this->getByIds($ids, true);
    }

    /**
     * Get tickets for any of an array of people.
     *
     * @param array $people
     * @param null  $limit
     *
     * @return array
     */
    public function getTicketsForPeople(array $people, $limit = null)
    {
        $ids = [];
        foreach ($people as $p) {
            if (is_object($p)) {
                $ids[] = $p->id;
            } else {
                $ids[] = $p;
            }
        }

        $ids = array_unique($ids);
        $ids = Arrays::removeFalsey($ids);

        if (!$ids) {
            return [];
        }

        $ticket_ids = $this->getEntityManager()->getConnection()->fetchAllCol("
            SELECT
                tickets.id,
                    CASE WHEN tickets.status =  'awaiting_agent' THEN 1
                    WHEN tickets.status =  'awaiting_user' THEN 2
                    WHEN tickets.status =  'resolved' THEN 3
                    WHEN tickets.status =  'archived' THEN 4
                    ELSE 3
                    END AS status_order
            FROM tickets
            LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
            WHERE tickets.person_id IN (?0) OR tickets_participants.person_id IN (?0)
            ORDER BY status_order ASC, tickets.date_status DESC
        ", [$ids], [Connection::PARAM_INT_ARRAY]);

        if (!$ticket_ids) {
            return [];
        }

        $tickets = $this->getByIds($ticket_ids, true);

        return $tickets;
    }

    /**
     * Count how many tickets a person has.
     *
     * @param Entity\Person $person
     * @param Entity\Person $agent
     * @param mixed         $status
     *
     * @return int
     */
    public function countTicketsForPerson(
        Entity\Person $person,
        Entity\Person $agent,
        $status = null,
        $departmentIds = []
    ) {
        $status = $status
            ? (' AND %alias%.status IN ("'.implode('","', (array) $status).'") ')
            : (' AND %alias%.status NOT IN ("'.implode('","', ['hidden']).'") ');

        $excludeNotesCondition = '';
        if (defined('DP_INTERFACE') && 'user' === DP_INTERFACE) {
            $excludeNotesCondition = ' AND (%alias%.date_last_agent_reply IS NOT NULL OR %alias%.date_last_user_reply IS NOT NULL) ';
        }

        $agentWherePermissions = '%alias%.agent_id = ?';
        $wheres                = [];
        $join                  = 'INNER JOIN tickets t2 ON t2.id = tp.ticket_id';

        if (!$agent->hasPerm('agent_tickets.view_unassigned')) {
            $wheres[] = '(%alias%.agent_id IS NOT NULL OR %alias%.agent_team_id IS NOT NULL)';
        }
        if (!$agent->hasPerm('agent_tickets.view_others')) {
            $wheres[] = '%alias%.agent_id IS NULL';
            $wheres[] = '%alias%.agent_team_id IS NULL';
        }

        if (!$person->isAgent()) {
            $params      = [$person->getId(), $person->getId()];
            $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT];

            if ($departmentIds) {
                $wheres[] = '%alias%.department_id IN (?)';

                $params = [
                    $person->getId(),
                    $agent->getId(),
                    $departmentIds,
                ];
                $paramsTypes = [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    Connection::PARAM_INT_ARRAY,
                ];
            }

            if ($wheres) {
                $constructedWhere = sprintf(' AND (%s OR (%s))', $agentWherePermissions, implode(' AND ', $wheres));
            } else {
                $constructedWhere = sprintf(' AND %s', $agentWherePermissions);
            }
            $where  = str_replace('%alias%', 't1', $status.$constructedWhere.$excludeNotesCondition);
            $where2 = str_replace('%alias%', 't2', $status.$constructedWhere.$excludeNotesCondition);

            $count = App::getDb()->fetchColumn('
                SELECT COUNT(DISTINCT(a.id))
                FROM (
                    SELECT t1.id FROM tickets AS t1 WHERE t1.person_id = ? '
                .$where
                .'
                    UNION
                    SELECT tp.ticket_id as id FROM tickets_participants AS tp
                    '.$join.' 
                    WHERE tp.person_id = ? '
                .$where2
                .') AS a',
                array_merge($params, $params),
                0,
                array_merge($paramsTypes, $paramsTypes)
            );
        } else {
            $params      = [$person->getId(), $agent->getId()];
            $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT];
            if ($departmentIds) {
                $wheres[]    = '%alias%.department_id IN (?)';
                $params      = [$person->getId(), $agent->getId(), $departmentIds];
                $paramsTypes = [\PDO::PARAM_INT, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY];
            }
            if ($wheres) {
                $where = sprintf('AND (%s OR (%s))', $agentWherePermissions, implode(' AND ', $wheres));
            } else {
                $where = sprintf('AND %s', $agentWherePermissions);
            }
            $where = str_replace('%alias%', 't1', $status.$where.$excludeNotesCondition);

            $count = App::getDb()->fetchColumn('
                SELECT COUNT(*) AS count FROM tickets AS t1 WHERE t1.person_id = ? '
                .$where,
                $params,
                0,
                $paramsTypes)
            ;
        }

        return $count;
    }

    /**
     * Returns array of:
     * - person: Number of their tickets
     * - org: Number of their org tickets, if they area a manger.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param null                               $status
     *
     * @return array
     */
    public function getCountInfoForPerson(Entity\Person $person, $status = null)
    {
        $counts = [
            'person' => $this->countTicketsForPerson($person),
            'org'    => 0,
        ];

        $status = $status ? (' AND tickets.status IN ("'.implode('","', (array) $status).'") ') : '';

        if ($person->organization && $person->organization_manager) {
            $counts['org'] = App::getDb()->fetchColumn('
                SELECT COUNT(DISTINCT tickets.id)
                FROM tickets
                LEFT JOIN tickets_participants ON tickets_participants.ticket_id = tickets.id
                WHERE
                    tickets.organization_id = ? '.$status.'
                    AND (tickets.date_last_agent_reply IS NOT NULL OR tickets.date_last_user_reply IS NOT NULL)
            ', [$person->getOrganizationId()]);
        }

        return $counts;
    }

    /**
     * Get all tickets that belong ot an org.
     *
     * @param Entity\Organization $org
     * @param null                $limit
     *
     * @return array
     */
    public function getOrganizationTickets(Entity\Organization $org, $limit = null)
    {
        $tickets = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.organization = ?1
            ORDER BY t.id DESC
        ')->setParameters([1 => $org])->setMaxResults($limit)->execute();

        return $tickets;
    }

    public function getRecentOrganizationTickets(Entity\Organization $org, $num = 30)
    {
        $ids = App::getDb()->fetchAllCol("
            SELECT id,
                CASE WHEN `status` =  'awaiting_agent' THEN 1
                WHEN `status` =  'awaiting_user' THEN 2
                WHEN `status` =  'resolved' THEN 3
                WHEN `status` =  'archived' THEN 4
                ELSE 3
                END AS status_order
            FROM tickets
            WHERE organization_id = {$org->id} AND status IN ('awaiting_agent', 'awaiting_user', 'archived', 'resolved')
            ORDER BY status_order ASC, urgency DESC
            LIMIT $num
        ", [$org->id]);

        if (!$ids) {
            return [];
        }

        $tickets = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.id IN (?1)
        ')->setParameters([1 => $ids])->setMaxResults($num)->execute();

        $tickets = \Orb\Util\Arrays::orderIdArray($ids, $tickets);

        return $tickets;
    }

    /**
     * COunt the total number of tickets that belong to an org.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     * @param null                                     $status
     *
     * @return int
     */
    public function countTicketsForOrganization(Entity\Organization $org, $status = null)
    {
        $status = $status ? (' AND status IN ("'.implode('","', (array) $status).'") ') : '';

        $count = App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM tickets
            WHERE organization_id = ? '.$status,
            [$org['id']]);

        return $count;
    }

    /**
     * Get the latest tickets from a particular user.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param int                                $max       The max number of results
     * @param bool                               $only_open
     *
     * @return array
     */
    public function getLatestByUser(Entity\Person $person, $max = 20, $only_open = false)
    {
        if ($only_open) {
            $status = [
                TicketStatus::STATUS_TYPE_AWAITING_AGENT,
                TicketStatus::STATUS_TYPE_AWAITING_USER,
            ];
        } else {
            $status = [
                TicketStatus::STATUS_TYPE_AWAITING_AGENT,
                TicketStatus::STATUS_TYPE_AWAITING_USER,
                TicketStatus::STATUS_TYPE_ARCHIVED,
                TicketStatus::STATUS_TYPE_RESOLVED,
            ];
        }

        $tickets = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.person = ?1 AND t.status IN(?2)
            ORDER BY t.id DESC
        ')->setMaxResults($max)->execute([1 => $person, 2 => $status]);

        return $tickets;
    }

    /**
     * Get the latest tickets awaiting user from a particular user.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param int                                $max    The max number of results
     *
     * @return array
     */
    public function getWaitingForReplyForPerson(Entity\Person $person, $max = 5)
    {
        $status = TicketStatus::STATUS_TYPE_AWAITING_USER;

        $tickets = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.person = ?1 AND t.status IN(?2)
            ORDER BY t.date_last_agent_reply DESC
        ')->setMaxResults($max)->execute([1 => $person, 2 => $status]);

        return $tickets;
    }

    /**
     * Executes a query to re-fill the ticket_search_active table.
     */
    public function fillSearchTable()
    {
        $field_ids = Entity\TicketSearchActive::getFieldNames();
        $field_ids = array_map(function ($f) {
            return "`$f`";
        }, $field_ids);
        $field_ids = implode(', ', $field_ids);

        App::getDb()->exec('TRUNCATE TABLE tickets_search_active');
        App::getDb()->exec("
            INSERT IGNORE INTO tickets_search_active ($field_ids) SELECT $field_ids
            FROM tickets
            WHERE status IN ('awaiting_agent', 'awaiting_user', 'resolved')
            ORDER BY id ASC
        ");
        $this->_em->getConnection()->executeQuery("REPLACE INTO settings SET name = 'core.last_searchtables_refill', value = '".time()."'");
        $this->_em->getConnection()->executeQuery("REPLACE INTO settings SET name = 'core.do_searchtables_refill', value = '0'");
    }

    /**
     * Checks the database for a duplicate ticket.
     *
     * Note: Make sure $ticket has its first message added or else the check
     * will fail.
     *
     * Returns the ticket ID if there was one found, or false if none found.
     *
     * @param TicketEntity $ticket
     * @param int          $secs_ago
     *
     * @return bool|TicketEntity
     */
    public function checkDupeTicket(TicketEntity $ticket = null, $secs_ago = 10800 /* 3 hours */)
    {
        if (!App::getSetting('core_tickets.enable_dupe_checking')) {
            return false;
        }

        $timesnip = date_create('-'.$secs_ago.' seconds');
        $check    = $this->getEntityManager()->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.ticket_hash = ?1 AND t.date_created > ?2
        ')->setParameters([1 => $ticket->getTicketHash(), 2 => $timesnip])->getResult();

        if (count($check)) {
            $check = array_shift($check);
        }
        if ($check && $check->getId() != $ticket->getId()) {
            return $check;
        }

        return false;
    }

    /**
     * Count tickets in each of the "archive" statuses:
     * - hidden.spam
     * - hidden.awaiting_validation
     * - resolved
     * - archived
     * - hidden.deleted.
     *
     * @return array
     */
    public function getArchiveCounts()
    {
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT IF(t.status = 'hidden', CONCAT('hidden', '.', ts.sys_id), t.status) AS status_code, COUNT(*)
            FROM tickets t
            LEFT JOIN ticket_statuses ts on t.ticket_status_id = ts.id
            WHERE
                t.status IN ('awaiting_user', 'archived', 'resolved', 'hidden')
            GROUP BY status_code
        ");
    }

    /**
     * @deprecated
     *
     * @param $validating_email
     *
     * @return array
     */
    public function getTicketIdsWithValidatingEmail($validating_email)
    {
        return [];
    }

    public function getTicketCountsForPeople(
        array $people,
        Entity\Person $agent
    ) {
        $permissionsHelper          = $agent->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'assign');

        $agentWherePermissions = '%alias%.agent_id = ?';
        $wheres                = [];
        $join                  = 'INNER JOIN tickets t2 ON t2.id = tp.ticket_id';

        if (!$agent->hasPerm('agent_tickets.view_unassigned')) {
            $wheres[] = '(%alias%.agent_id IS NOT NULL OR %alias%.agent_team_id IS NOT NULL)';
        }
        if (!$agent->hasPerm('agent_tickets.view_others')) {
            $wheres[] = '%alias%.agent_id IS NULL';
            $wheres[] = '%alias%.agent_team_id IS NULL';
        }

        $ids = [];
        foreach ($people as $p) {
            $ids[] = $p['id'];
        }

        /** @var Connection $db */
        $db = $this->_em->getConnection();

        $peopleIds = $db->fetchAllCol('SELECT id FROM people WHERE id IN (?) AND is_agent = 0', [$ids], [Connection::PARAM_INT_ARRAY]);
        $agentIds  = $db->fetchAllCol('SELECT id FROM people WHERE id IN (?) AND is_agent = 1', [$ids], [Connection::PARAM_INT_ARRAY]);

        if ($peopleIds) {
            $params      = [$peopleIds, $agent->getId()];
            $paramsTypes = [Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT];

            if ($allowedTicketDepartmentIds) {
                $wheres[] = '%alias%.department_id IN (?)';

                $params = [
                    $peopleIds,
                    $agent->getId(),
                    $allowedTicketDepartmentIds,
                ];
                $paramsTypes = [
                    Connection::PARAM_INT_ARRAY,
                    \PDO::PARAM_INT,
                    Connection::PARAM_INT_ARRAY,
                ];
            }

            if ($wheres) {
                $constructedWhere = sprintf(' AND (%s OR (%s))', $agentWherePermissions, implode(' AND ', $wheres));
            } else {
                $constructedWhere = sprintf(' AND %s', $agentWherePermissions);
            }
            $where  = str_replace('%alias%', 't1', $constructedWhere);
            $where2 = str_replace('%alias%', 't2', $constructedWhere);

            $countsPeople = $this->getEntityManager()->getConnection()->fetchAllKeyValue('
                SELECT person_id, COUNT(DISTINCT(id)) FROM (
                    SELECT t1.id, t1.person_id FROM tickets as t1 
                    WHERE t1.person_id IN (?)
                    '.$where.'
                    UNION ALL
                    SELECT tp.person_id, tp.ticket_id as id FROM tickets_participants as tp
                    '.$join.'
                    WHERE tp.person_id IN (?)
                    '.$where2.'
                ) a
                GROUP BY person_id
            ', array_merge($params, $params), array_merge($paramsTypes, $paramsTypes));
        } else {
            $countsPeople = [];
        }

        if ($agentIds) {
            $countsAgents = $this->getEntityManager()->getConnection()->fetchAllKeyValue('
                SELECT person_id, COUNT(*) FROM tickets WHERE person_id IN (?)
                GROUP BY person_id
            ', [$agentIds], [Connection::PARAM_INT_ARRAY]);
        } else {
            $countsAgents = [];
        }

        return $countsPeople + $countsAgents;
    }

    /**
     * @param $ticket_ref
     * @param PersonEntity $person_context
     * @param null         $matched_type
     *
     * @return TicketEntity
     */
    public function getTicketByPublicId($ticket_ref, PersonEntity $person_context = null, &$matched_type = null)
    {
        if ($person_context && !$person_context->getId()) {
            $person_context = null;
        }

        if ($person_context) {
            $try_order = ['id', 'ref', 'ptac'];
        } else {
            $try_order = ['id', 'ptac', 'ref'];
        }

        foreach ($try_order as $lookup_type) {
            switch ($lookup_type) {
                case 'id':
                    if (Numbers::isInteger($ticket_ref)) {
                        $ticket = $this->_em->find('DeskPRO:Ticket', $ticket_ref);
                        if ($ticket) {
                            $matched_type = 'id';

                            return $ticket;
                        }
                    }
                    break;

                case 'ref':
                    $ticket = $this->_em->getRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);
                    if ($ticket) {
                        $matched_type = 'ref';

                        return $ticket;
                    }
                    break;

                case 'ptac':

                    $ticket = $this->_em->getRepository('DeskPRO:Ticket')->getByAccessCode($ticket_ref);

                    if ($ticket) {
                        $matched_type = 'ptac';

                        return $ticket;
                    }
                    break;
            }
        }

        return;
    }

    /**
     * Find all linked tickets.
     *
     * @param TicketEntity $parent_ticket
     *
     * @return array
     */
    public function getLinkedTickets(TicketEntity $parent_ticket)
    {
        return $this->_em->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.parent_ticket = ?0 AND t.status != 'hidden'
            ORDER BY t.id ASC
        ")->execute([$parent_ticket]);
    }

    /**
     * Find a ticket linked to the chat.
     *
     * @param ChatConversationEntity $chat
     *
     * @return TicketEntity
     */
    public function getTicketLinkedToChat(ChatConversationEntity $chat)
    {
        $linked = $this->_em->createQuery('
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.linked_chat = ?0
        ')->execute([$chat]);

        if (count($linked)) {
            return current($linked);
        }

        return;
    }

    /**
     * Runs a COUNT query against all awaiting_agent tickets and returns the number of tickets
     * in each urgency.
     *
     * @return array
     */
    public function countTicketsByUrgency()
    {
        $counts = $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT urgency, COUNT(*) AS count
            FROM tickets
            WHERE status = 'awaiting_agent'
            GROUP BY urgency
        ");

        return $counts;
    }

    /**
     * @param int $offlineOffset offset in seconds from now, when the agents considered as 'offline'
     *
     * @return int
     */
    public function unlockOfflineAgentsTickets($offlineOffset = 120)
    {
        $cut_ts  = strtotime(-(int) $offlineOffset.' seconds');
        $datecut = date('Y-m-d H:i:s', $cut_ts);

        $db = App::$container->getDb();

        // map of ticket->agent of tickets that are still locked after the offset
        $agents_to_tickets = $db->fetchAllGrouped('
            SELECT id, COALESCE(locked_by_agent, 0) AS locked_by_agent
            FROM tickets
            WHERE date_locked < ?
            ORDER BY date_locked ASC
            LIMIT 2500
        ', [$datecut], 'locked_by_agent', null, 'id');

        if (!$agents_to_tickets) {
            return 0;
        }

        // Check session times for these agents
        $agents_to_times = $db->fetchAllKeyValue('
            SELECT sessions.person_id, sessions.date_last
            FROM sessions
            LEFT OUTER JOIN sessions AS lookup ON (
                lookup.person_id = sessions.person_id
                AND sessions.date_last < lookup.date_last
            )
            WHERE sessions.person_id IN (?) AND lookup.person_id IS NULL
        ', [array_keys($agents_to_tickets)], [Connection::PARAM_INT_ARRAY]);

        $release_locks = [];
        foreach ($agents_to_tickets as $agent_id => $ticket_ids) {
            if (!isset($agents_to_times[$agent_id])) {
                $release_locks = array_merge($release_locks, $ticket_ids);
            } else {
                $ts = \DateTime::createFromFormat('Y-m-d H:i:s', $agents_to_times[$agent_id])->getTimestamp();
                if ($ts < $cut_ts) {
                    $release_locks = array_merge($release_locks, $ticket_ids);
                }
            }
        }

        if ($release_locks) {
            // re-fetch to make sure we have valid records
            $release_locks = $db->fetchAllCol('
                SELECT id
                FROM tickets
                WHERE date_locked < ? AND id IN (?)
            ', [$datecut, $release_locks], [\PDO::PARAM_STR, Connection::PARAM_INT_ARRAY]);
        }

        return $this->unlockTickets($release_locks);
    }

    public function unlockTicketsByTime($offset)
    {
        if (!$offset) {
            return 0;
        }

        $db = App::$container->getDb();

        $cut_ts  = strtotime(-(int) $offset.' seconds');
        $datecut = date('Y-m-d H:i:s', $cut_ts);

        $ticket_ids = $db->fetchAllCol('
            SELECT id
            FROM tickets
            WHERE date_locked < ?
        ', [$datecut]);

        return $this->unlockTickets($ticket_ids);
    }

    public function unlockTickets(array $ticket_ids)
    {
        if (!$ticket_ids) {
            return 0;
        }

        $db              = App::$container->getDb();
        $eventDispatcher = App::$container->get('event_dispatcher');

        $db->updateIn('tickets', [
            'date_locked'     => null,
            'locked_by_agent' => null,
        ], $ticket_ids);

        if (count($ticket_ids) < 250) {
            foreach ($ticket_ids as $id) {
                $eventDispatcher->dispatch(
                    TicketUpdatedEvent::EVENT_NAME,
                    new TicketUpdatedEvent(
                        'agent-notification.tickets.locked-status',
                        [
                            'ticket_id'      => $id,
                            'is_locked'      => false,
                            'locked_by'      => null,
                            'locked_by_name' => null,
                            'via_person'     => null,
                        ]
                ));
            }
        }

        return count($ticket_ids);
    }

    public function getTicketsForPerson(PersonEntity $person, $offset, $limit, $sort)
    {
        return $this->getQueryForPerson($person, $sort)->setFirstResult($offset)->setMaxResults($limit)->getResult();
    }

    public function countTicketsForPerson2(Entity\Person $person)
    {
        list($parts, $params, $parts_union) = $this->getQueryPartsForPerson($person);

        return $this->getEntityManager()->getConnection()->fetchColumn(
            "SELECT COUNT(DISTINCT id) FROM ($parts_union) AS t",
            $params
        );
    }

    /**
     * @param $number
     *
     * @return TicketEntity|null
     */
    public function getLastTicketForNumber($number)
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->join('t.messages', 'm')
            ->join(TicketMessageVoicePhoneCall::class, 'a', Join::WITH, 'a.message = m.id')
            ->join('a.phoneCall', 'p')
            ->where(
                'p.externalNumber = :number',
                't.status IN (:statuses)'
            )
            ->setParameter('number', $number)
            ->setParameter('statuses', [
                TicketStatus::STATUS_TYPE_AWAITING_USER,
                TicketStatus::STATUS_TYPE_AWAITING_AGENT,
            ])
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    protected function getQueryPartsForPerson(Entity\Person $person)
    {
        $parts  = [];
        $params = [];

        $parts[]  = '(SELECT id FROM tickets WHERE person_id = ? ORDER BY id DESC LIMIT 2000)';
        $params[] = $person->id;

        if (!$person->is_agent) {
            $parts[]  = '(SELECT ticket_id FROM tickets_participants WHERE person_id = ? ORDER BY ticket_id DESC LIMIT 2000)';
            $params[] = $person->id;
        }

        $parts_union = implode("\nUNION\n", $parts);

        return [$parts, $params, $parts_union];
    }

    /**
     * @param PersonEntity $person
     * @param null         $sort
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getQueryForPerson(Entity\Person $person, $sort = null)
    {
        list($parts, $params, $parts_union) = $this->getQueryPartsForPerson($person);

        $ids = $this->getEntityManager()->getConnection()->fetchAllCol(
            "SELECT DISTINCT id FROM ($parts_union) AS t",
            $params
        );

        if (!$ids) {
            $ids = [0];
        }

        $qb = $this->createQueryBuilder('t');
        $qb->select('t');
        $qb->where('t.status != \'hidden\' AND (t.date_last_agent_reply IS NOT NULL OR t.date_last_user_reply IS NOT NULL)');
        $qb->andWhere('t.id IN (:ids)');
        $qb->setParameter('ids', $ids);

        switch ($sort) {
            case 'department':
                $qb->leftJoin('t.department', 'd');
                $qb->leftJoin('d.parent', 'd_parent');
                $qb->addOrderBy('d_parent.display_order, d.display_order, t.id', 'DESC');
                break;

            case 'last_reply':
                $qb->addOrderBy('t.date_last_user_reply', 'DESC');
                break;

            case 'date_created':
            default:
                $qb->addOrderBy('t.id', 'DESC');
        }

        return $qb->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getReportAssociations()
    {
        return [
            'contextual_data' => [
                'conditions'   => '%1$s.owner_id = %2$s.id',
                'targetEntity' => CustomFieldDataEntity::class,
            ],
        ];
    }
}
