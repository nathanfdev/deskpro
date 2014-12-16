<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketDeleted as TicketDeletedEntity;
use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class Ticket extends AbstractEntityRepository
{
    /**
     * The ticket is
     *
     * @param  Person                                 $person
     * @return TicketEntity
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findMostRecentSmsTicketFromPerson(PersonEntity $person, $date_last_reply = null)
    {
        if (!$date_last_reply) {
            $date_last_reply = new \DateTime("now - 3 days");
        }

        $query = $this->getEntityManager()->createQuery(
            "
                SELECT t
                FROM DeskPRO:Ticket t
                WHERE t.person = :person
                AND t.creation_system = :creation_system
                AND (t.date_last_user_reply > :date_last_reply OR t.date_last_agent_reply > :date_last_reply)
                ORDER BY t.date_last_user_reply DESC
            "
            )
            ->setMaxResults(1)
            ->setParameter('date_last_reply', $date_last_reply)
            ->setParameter('person', $person->getId())
            ->setParameter('creation_system', IncomingSmsProcessor::TICKET_CREATION_SYSTEM);

        $ticket = $query->getOneOrNullResult();

        return $ticket;
    }

    /**
     * Find a ticket by its TAC
     *
     * @param $access_code
     * @return null
     */
    public function getByAccessCode($access_code)
    {
        $info = Entity\Ticket::decodeAccessCode($access_code);
        if (!$info) {
            return null;
        }

        $rec = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.id = :ticket_id AND t.auth = :auth
        ")->setParameters($info)->setMaxResults(1)->getOneOrNullResult();

        if (!$rec) {
            // Try to find it through merges
            $del_ticket = $this->getEntityManager()->createQuery("
                SELECT t
                FROM DeskPRO:TicketDeleted t
                WHERE t.ticket_id = :ticket_id AND t.old_ptac = :auth
            ")->setParameters($info)->setMaxResults(1)->getOneOrNullResult();

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
     * @return Ticket
     */
    public function findTicketId($ticket_id)
    {
        $ticket = $this->_em->find('DeskPRO:Ticket', $ticket_id);
        if ($ticket) {
            return $ticket;
        }

        $del_ticket = $this->_em->createQuery("
            SELECT t
            FROM DeskPRO:TicketDeleted t
            WHERE t.ticket_id = ?0
        ")->setParameters(array($ticket_id))->setMaxResults(1)->getOneOrNullResult();

        if (!$del_ticket) {
            return null;
        }

        return $this->resolveDeletedTicket($del_ticket);
    }


    /**
     * Find a ticket ref and if it cant be found, try to find it through deleted records.
     *
     * @param $ticket_ref
     * @return Ticket
     */
    public function findTicketRef($ticket_ref)
    {
        $ticket = $this->findOneBy(array('ref' => $ticket_ref));
        if ($ticket) {
            return $ticket;
        }

        $del_ticket = $this->_em->createQuery("
            SELECT t
            FROM DeskPRO:TicketDeleted t
            WHERE t.old_ref = ?0
        ")->setParameters(array($ticket_ref))->setMaxResults(1)->getOneOrNullResult();

        if (!$del_ticket) {
            return null;
        }

        return $this->resolveDeletedTicket($del_ticket);
    }

    public function searchTicketRef($ref)
    {
        $ref = str_replace(array('%', '_'), array('\\%', '\\_'), $ref);
        $tickets = $this->_em->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.ref LIKE ?0
            ORDER BY t.id DESC
        ")->setParameters(array($ref."%"))->setMaxResults(10)->execute();

        $new_ticket_ids = $this->getEntityManager()->getConnection()->fetchAll("
            SELECT new_ticket_id
            FROM tickets_deleted
            WHERE old_ref LIKE ?
        ", array($ref));
        if ($new_ticket_ids) {
            $other_tickets = $this->getByIds($new_ticket_ids);
            if (count($other_tickets)) {
                $ret = array();
                foreach ($tickets as $t) { $ret[$t->getId()] = $t; }
                foreach ($other_tickets as $t) { $ret[$t->getId()] = $t; }
                $tickets = array_values($ret);
            }
        }

        return $tickets;
    }


    /**
     * @param \Application\DeskPRO\Entity\TicketDeleted $del_ticket
     */
    public function resolveDeletedTicket(TicketDeletedEntity $del_ticket)
    {
        $new_delticket = $del_ticket;
        while ($new_delticket) {
            $del_ticket = $new_delticket;
            $new_delticket = $this->getEntityManager()->createQuery("
                SELECT t
                FROM DeskPRO:TicketDeleted t
                WHERE t.ticket_id = ?0
            ")->setParameters(array($del_ticket->new_ticket_id))->setMaxResults(1)->getOneOrNullResult();
        }

        if (!$del_ticket) {
            return null;
        }
        $ticket = $this->find($del_ticket->new_ticket_id);

        return $ticket;
    }


    /**
     * Get tickets by specific ids
     *
     * @param  array $ids
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

        if (!$ids) return array();

        $tickets = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.id IN(?0)
            ORDER BY t.id ASC
        ")->execute(array($ids));

        return $tickets;
    }

    /**
     * Fetches full ticket object graphs for ticket listing results
     *
     * @param  array       $ids
     * @return array|mixed
     */
    public function getTicketsResultsFromIds(array $ids)
    {
        if (!$ids) return array();

        // Must be numerically indexed
        $ids = array_values($ids);

        $tickets = $this->getEntityManager()->createQuery("
            SELECT t, slas
            FROM DeskPRO:Ticket t INDEX BY t.id
            LEFT JOIN t.ticket_slas slas
            WHERE t.id IN(?1)
            ORDER BY t.id ASC
        ")->setParameter(1, $ids)->execute();

        return $tickets;
    }

    /**
     * Get all tickets a person owns, or is a participant in.
     * This is usually used to fetch a list of tickets for an end-user.
     *
     * @return array
     */
    public function getPersonTickets(Entity\Person $person, $limit = null, $sort_by = null, $sort_order = 'DESC')
    {
        if ($person->is_agent) {
            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE person_id = ?
                ORDER BY id DESC
                LIMIT 2000
            ", array($person->id));
        } else {
            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id FROM tickets WHERE person_id = ?
                UNION
                SELECT ticket_id FROM tickets_participants WHERE person_id = ?
            ", array($person->id, $person->id));
        }

        if (!$ids) {
            return array();
        }

        if ($sort_by === 'date_last_reply') {
            $ids = $this->getEntityManager()->getConnection()->fetchAllCol(
                '
                                SELECT id
                                FROM tickets
                                WHERE id IN (?)
                ORDER BY GREATEST(
+					COALESCE(date_last_user_reply,0),
+					COALESCE(date_last_agent_reply,0)
+				) DESC
            ',
            array($ids), array(Connection::PARAM_INT_ARRAY));

        } elseif ($sort_by == 'status') {
            $ids = App::getDb()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE id IN (?)
                ORDER BY FIELD(tickets.status, 'awaiting_agent', 'awaiting_user', 'resolved', 'archived', 'hidden') ASC, IF(tickets.status = 'awaiting_agent', tickets.urgency, 0) DESC, tickets.id DESC
            ", array($ids), array(Connection::PARAM_INT_ARRAY));
        } elseif ($sort_by && in_array(strtolower($sort_by), $this->_em->getClassMetadata('DeskPRO:Ticket')->getFieldNames())) {
            $sort_by = strtolower($sort_by);

            $sort_order = strtolower($sort_order);
            $sort_order = in_array($sort_order, array('asc', 'desc')) ? $sort_order : 'DESC';

            $ids = $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE id IN (?)
                ORDER BY $sort_by $sort_order
            ", array($ids), array(Connection::PARAM_INT_ARRAY)
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
     * Get tickets for any of an array of people
     *
     * @param  array $people
     * @param  null  $limit
     * @return array
     */
    public function getTicketsForPeople(array $people, $limit = null)
    {
        $ids = array();
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
            return array();
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
        ", array($ids), array(Connection::PARAM_INT_ARRAY));

        if (!$ticket_ids) {
            return array();
        }

        $tickets = $this->getByIds($ticket_ids, true);

        return $tickets;
    }

    /**
     * Count how many tickets a person has
     *
     * @param  \Application\DeskPRO\Entity\Person $person
     * @param  null                               $status
     * @return int
     */
    public function countTicketsForPerson(Entity\Person $person, $status = null)
    {
        if ($status) {
            $status = (array)$status;
            foreach ($status as &$s) {
                $s = "'$s'";
            }
            $status = implode(',', $status);
        }

        if ($person->is_agent) {
            $count = App::getDb()->fetchColumn("
                SELECT COUNT(*)
                FROM tickets
                WHERE tickets.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
            ", array($person->id));
        } else {
            $count = App::getDb()->fetchColumn("
                SELECT SUM(count)
                FROM (
                    SELECT COUNT(*) AS count FROM tickets WHERE tickets.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                    UNION
                    SELECT COUNT(*) AS count FROM tickets_participants WHERE tickets_participants.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                ) a
            ", array($person->id, $person->id));
        }

        return $count;
    }

    /**
     * Returns array of:
     * - person: Number of their tickets
     * - org: Number of their org tickets, if they area a manger
     *
     * @param  \Application\DeskPRO\Entity\Person $person
     * @param  null                               $status
     * @return array
     */
    public function getCountInfoForPerson(Entity\Person $person, $status = null)
    {
        if ($status) {
            $status = (array)$status;
            foreach ($status as &$s) {
                $s = "'$s'";
            }
            $status = implode(',', $status);
        }

        $counts = array(
            'person' => 0,
            'org'    => 0,
        );

        if ($person->is_agent) {
            $count = App::getDb()->fetchColumn("
                SELECT COUNT(*)
                FROM tickets
                WHERE tickets.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
            ", array($person->id));
        } else {
            if ($person->organization && $person->organization_manager) {
                $count = array_sum(App::getDb()->fetchAllCol("
                    (
                        SELECT COUNT(DISTINCT tickets.id)
                        FROM tickets
                        WHERE tickets.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                    )
                    UNION
                    (
                        SELECT COUNT(DISTINCT tickets.id)
                        FROM tickets
                        LEFT JOIN tickets_participants ON tickets_participants.ticket_id = tickets.id
                        WHERE tickets_participants.person_id = ? AND tickets.organization_id != ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                    )
                ", array($person->id, $person->id, $person->getOrganizationId())));
            } else {
                $count = App::getDb()->fetchColumn("
                    (
                        SELECT COUNT(DISTINCT tickets.id)
                        FROM tickets
                        WHERE tickets.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                    )
                    UNION
                    (
                        SELECT COUNT(DISTINCT tickets.id)
                        FROM tickets
                        LEFT JOIN tickets_participants ON tickets_participants.ticket_id = tickets.id
                        WHERE tickets_participants.person_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                    )
                ", array($person->id, $person->id));
            }
        }

        $counts['person'] = $count;

        if ($person->organization && $person->organization_manager) {
            $allowed_ids = $person->getPermissionsManager()->Departments->getAllowedIds('tickets');
            if ($allowed_ids) {
                $counts['org'] = App::getDb()->fetchColumn("
                    SELECT COUNT(DISTINCT tickets.id)
                    FROM tickets
                    LEFT JOIN tickets_participants ON tickets_participants.ticket_id = tickets.id
                    WHERE
                        tickets.organization_id = ? " . ($status ? " AND tickets.status IN ($status) " : '') . "
                        AND tickets.department_id IN (".implode(',', $allowed_ids).")
                ", array($person->getOrganizationId()));
            }
        }

        return $counts;
    }

    /**
     * Get all tickets that belong ot an org
     *
     * @return array
     */
    public function getOrganizationTickets(Entity\Organization $org, $limit = null)
    {
        $tickets = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.organization = ?1
            ORDER BY t.id DESC
        ")->setParameters(array(1=>$org))->setMaxResults($limit)->execute();

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
        ", array($org->id));

        if (!$ids) {
            return array();
        }

        $tickets = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t INDEX BY t.id
            WHERE t.id IN (?1)
        ")->setParameters(array(1=>$ids))->setMaxResults($num)->execute();

        $tickets = \Orb\Util\Arrays::orderIdArray($ids, $tickets);

        return $tickets;
    }

    /**
     * COunt the total number of tickets that belong to an org
     *
     * @param  \Application\DeskPRO\Entity\Organization $org
     * @param  null                                     $status
     * @return int
     */
    public function countTicketsForOrganization(Entity\Organization $org, $status = null)
    {
        if ($status) {
            $status = (array)$status;
            foreach ($status as &$s) {
                $s = "'$s'";
            }
            $status = implode(',', $status);
        }

        $count = App::getDb()->fetchColumn("
            SELECT COUNT(*)
            FROM tickets
            WHERE organization_id = ? " . ($status ? " AND status IN ($status) " : '') . "
        ", array($org['id']));

        return $count;
    }

    /**
     * Get the latest tickets from a particular user
     *
     * @param  \Application\DeskPRO\Entity\Person $person
     * @param  int                                $max    The max number of results
     * @return array
     */
    public function getLatestByUser(Entity\Person $person, $max = 20, $only_open = false)
    {
        if ($only_open) {
            $status = array(
                TicketEntity::STATUS_AWAITING_AGENT,
                TicketEntity::STATUS_AWAITING_USER
            );
        } else {
            $status = array(
                TicketEntity::STATUS_AWAITING_AGENT,
                TicketEntity::STATUS_AWAITING_USER,
                TicketEntity::STATUS_ARCHIVED,
                TicketEntity::STATUS_RESOLVED
            );
        }

        $tickets = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.person = ?1 AND t.status IN(?2)
            ORDER BY t.id DESC
        ")->setMaxResults($max)->execute(array(1=> $person, 2=> $status));

        return $tickets;
    }

    /**
     * Executes a query to re-fill the ticket_search_active table
     */
    public function fillSearchTable()
    {
        $field_ids = Entity\TicketSearchActive::getFieldNames();
        $field_ids = array_map(function ($f) { return "`$f`"; }, $field_ids);
        $field_ids = implode(', ', $field_ids);

        App::getDb()->exec("TRUNCATE TABLE tickets_search_active");
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
     * @param  \Application\DeskPRO\Entity\TicketMessage $message
     * @param  int                                       $secs_ago
     * @return bool|mixed
     */
    public function checkDupeTicket($ticket = null, $secs_ago = 10800 /* 3 hours */)
    {
        if (App::getConfig('debug.disable_dupe_check')) {
            return false;
        }

        $timesnip = date_create('-' . $secs_ago . ' seconds');

        $check = $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.ticket_hash = ?1 AND t.date_created > ?2
        ")->setParameters(array(1=> $ticket['ticket_hash'], 2=>$timesnip))->getResult();
        if (count($check)) {
            $check = array_shift($check);
        }

        if ($check && $check->id != $ticket->id) {
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
     * - hidden.deleted
     *
     * @return array
     */
    public function getArchiveCounts()
    {
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
            SELECT IF(status = 'hidden', CONCAT('hidden', '.', hidden_status), status) AS status_code, COUNT(*)
            FROM tickets
            WHERE
                status IN ('awaiting_user', 'archived', 'resolved', 'hidden')
            GROUP BY status_code
        ");
    }

    public function getTicketIdsWithValidatingEmail($validating_email)
    {
        if (is_object($validating_email)) {
            $validating_email = $validating_email->getId();
        }

        $validating_email = (int)$validating_email;

        return $this->getEntityManager()->getConnection()->fetchAllCol("
            SELECT id
            FROM tickets
            WHERE person_email_validating_id = ?
            ORDER BY id DESC
        ", array($validating_email));
    }

    public function getTicketIdsWithEmail($email, $for_validation = false)
    {
        if (is_object($email)) {
            $email = $email->getId();
        }

        $email = (int)$email;

        if ($for_validation) {
            return $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT tickets.id
                FROM tickets
                LEFT JOIN people ON (people.id = tickets.person_id)
                LEFT JOIN people_emails ON (people_emails.id = people.primary_email_id)
                WHERE (person_email_id = ? OR (person_email_id IS null AND people_emails.id = ?)) AND status = 'hidden' AND hidden_status = 'validating'
                ORDER BY tickets.id DESC
            ", array($email, $email));
        } else {
            return $this->getEntityManager()->getConnection()->fetchAllCol("
                SELECT id
                FROM tickets
                WHERE person_email_id = ?
                ORDER BY id DESC
            ", array($email));
        }
    }

    public function getTicketCountsForPeople(array $people)
    {
        $ids = array();
        foreach ($people as $p) {
            $ids[] = $p->id;
        }

        if (!$ids) {
            return array();
        }

        return $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT person_id, COUNT(*)
            FROM tickets
            WHERE person_id IN (?)
            GROUP BY person_id
        ', array($ids), array(Connection::PARAM_INT_ARRAY));
    }

    /**
     * @param  mixed                              $id
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicketByPublicId($ticket_ref, PersonEntity $person_context = null, &$matched_type = null)
    {
        if ($person_context && !$person_context->getId()) {
            $person_context = null;
        }

        if ($person_context) {
            $try_order = array('id', 'ref', 'ptac');
        } else {
            $try_order = array('id', 'ptac', 'ref');
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

        return null;
    }

    /**
     * Find all linked tickets
     *
     * @param  TicketEntity $parent_ticket
     * @return array
     */
    public function getLinkedTickets(TicketEntity $parent_ticket)
    {
        return $this->_em->createQuery("
            SELECT t
            FROM DeskPRO:Ticket t
            WHERE t.parent_ticket = ?0 AND t.status != 'hidden'
            ORDER BY t.id ASC
        ")->execute(array($parent_ticket));
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
     * @param  int $offlineOffset offset in seconds from now, when the agents considered as 'offline'
     * @return int
     */
    public function unlockOfflineAgentsTickets($offlineOffset = 120)
    {
        $datecut = date('Y-m-d H:i:s', strtotime(- (int) $offlineOffset . ' seconds'));

        return $this->_em->getConnection()->executeUpdate("
            UPDATE tickets
            JOIN sessions ON (sessions.person_id = tickets.locked_by_agent AND sessions.date_last IS NOT NULL AND sessions.date_last < ?)
            SET tickets.locked_by_agent = null, tickets.date_locked = null
            WHERE tickets.locked_by_agent IS NOT NULL
        ", array($datecut));
    }
}
