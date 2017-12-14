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
use Application\DeskPRO\BigMode;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\DepartmentPermission as DepartmentPermissionEntity;
use Application\DeskPRO\Entity\Organization as OrganizationEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\PhoneNumber as PhoneNumberEntity;
use Application\DeskPRO\Entity\Usergroup as UsergroupEntity;
use Application\DeskPRO\EntityRepository\Helper\IdentityHelper;
use Doctrine\DBAL\LockMode;
use Orb\Util\Strings;

class Person extends AbstractEntityRepository
{
    /** @var IdentityHelper */
    protected $identity_helper;

    public function findOneByPhoneNumber($from_number)
    {
        $phone_number = $this->getEntityManager()->getRepository('DeskPRO:PhoneNumber')->findByNumber($from_number);

        if (!$phone_number) {
            return; // didnt find the number in the db
        }

        $query = $this->getEntityManager()->createQuery(
            '
            SELECT p
            FROM DeskPRO:Person p
            WHERE :found_phone_number MEMBER OF p.phone_numbers
        '
        );

        $query->setMaxResults(1)->setParameter('found_phone_number', $phone_number);

        return $query->getOneOrNullResult();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Helper\IdentityHelper
     */
    public function getIdentityHelper()
    {
        if (!$this->identity_helper) {
            $this->identity_helper = new Helper\IdentityHelper($this->getEntityManager(), $this);
        }

        return $this->identity_helper;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getAgents()
    {
        if (($agents = $this->getIdentityHelper()->getCollection('agents')) === null) {
            $agents = $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Person p INDEX BY p.id
                WHERE p.is_agent = true AND p.is_deleted = false
                ORDER BY p.first_name ASC, p.last_name ASC
            ')->execute();

            $this->getIdentityHelper()->setCollectionFromResults('agents', $agents);
        }

        return $agents;
    }

    public function getAgent($id)
    {
        $agents = $this->getAgents();

        return isset($agents[$id]) ? $agents[$id] : null;
    }

    public function getDeletedAgents()
    {
        $deleted_agents = $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p INDEX BY p.id
            WHERE p.is_agent = true AND p.is_deleted = true
            ORDER BY p.first_name ASC, p.last_name ASC
        ')->execute();

        return $deleted_agents;
    }

    /**
     * Gets a count of active agents (suitable for license checks).
     *
     * @return int
     */
    public function getActiveAgentsCount()
    {
        return $this->_em->getConnection()->fetchColumn('
            SELECT COUNT(*)
            FROM people
            WHERE is_agent = 1 AND is_deleted = 0
        ');
    }

    /**
     * Give a department and get back the agents that are in that department.
     *
     * Currently this is defined as anyone with a "FULL" permission to the
     * ticket system.
     *
     * @param Department|int $department the actual department or the id of it
     *
     * @return PersonEntity[]
     */
    public function getAgentsInDepartment($department)
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p
            JOIN p.department_permissions dep_per
            WHERE (p.is_agent = true AND p.is_deleted = false)
            AND dep_per.department = :department
            AND dep_per.name = :permission
            AND dep_per.app = :app
            AND dep_per.value = 1
            AND dep_per.is_active = 1
            ORDER BY p.last_name ASC, p.first_name ASC
        ')
            ->setParameter('department', $department)
            ->setParameter('permission', DepartmentPermissionEntity::FULL)
            ->setParameter('app', DepartmentPermissionEntity::APP_TICKETS)
            ->execute();
    }

    /**
     * @param \Application\DeskPRO\Entity\AgentTeam[] $teams
     *
     * @return PersonEntity[]
     */
    public function getAgentsInTeams($teams)
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p
            JOIN p.teams t
            WHERE p.is_agent = true
            AND p.is_deleted = false
            AND t.id IN (:teams)
        ')
            ->setParameter('teams', $teams)
            ->execute();
    }

    public function findAgentByName($name)
    {
        try {
            $priority = $this->getEntityManager()->createQuery("
                SELECT p
                FROM DeskPRO:Person p
                WHERE CONCAT(first_name, ' ', last_name) LIKE ?1 AND p.is_deleted = false
            ")->setParameter(1, "%$name%")->getSingleResult();
        } catch (\Exception $e) {
            return;
        }

        return $priority;
    }

    public function getAgentName($id)
    {
        $all = $this->getAgentNames([$id]);

        if (isset($all[$id])) {
            return $all[$id];
        }

        return;
    }

    /**
     * Get agent names.
     *
     * @param null $forIds
     *
     * @return array
     */
    public function getAgentNames($forIds = null)
    {
        if ($forIds && !is_array($forIds)) {
            $forIds = [$forIds];
        }

        // No names to return
        if (is_array($forIds) && !$forIds) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(
                'p.id',
                "(CASE
                    WHEN (LENGTH(p.first_name) > 0 AND LENGTH(p.last_name) > 0) THEN CONCAT(p.first_name, ' ', p.last_name)
                    WHEN LENGTH(p.name) > 0 THEN p.name
                    WHEN LENGTH(p.last_name) > 0 THEN p.last_name
                    WHEN LENGTH(p.first_name) > 0 THEN p.first_name
                    ELSE ''
                END) as name
                ",
                'pe.email'
            )
            ->from(PersonEntity::class, 'p')
            ->leftJoin('p.primary_email', 'pe')
            ->where(
                'p.is_agent = 1',
                'p.is_disabled = 0',
                'p.is_deleted = 0'
            )
            ->orderBy('name', 'ASC')
        ;

        if ($forIds) {
            $qb->andWhere('p.id IN (:ids)');
            $qb->setParameter('ids', $forIds);
        }

        $agents = $qb->getQuery()->getResult();
        $names  = [];
        foreach ($agents as $agent) {
            if ($agent['name']) {
                $name = $agent['name'];
            } elseif ($agent['email']) {
                $name = Strings::getNameFromEmail($agent['email']);
            } else {
                $name = 'ID-'.$agent['id'];
            }

            $names[$agent['id']] = $name;
        }

        return $names;
    }

    public function getPersonNames($for_ids)
    {
        $for_ids = (array) $for_ids;
        if (!$for_ids) {
            return [];
        }

        return $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT id, name
            FROM people
            WHERE id IN (?)
            ORDER BY name
        ', [$for_ids], [Connection::PARAM_INT_ARRAY]);
    }

    /**
     * Get all online and active (not away) agents.
     *
     * @param bool $ids_only
     *
     * @return array
     */
    public function getActiveAgents($ids_only = false)
    {
        $cutoff = date('Y-m-d H:i:s', time() - App::getSetting('core_chat.agent_timeout'));

        $or_id = '';
        if (App::getCurrentPerson() && App::getCurrentPerson()->is_agent) {
            $or_id = 'OR s.person = :person';
        }

        $sessions_q = App::getOrm()->createQuery("
            SELECT s,p
            FROM DeskPRO:Session s
            LEFT JOIN s.person p
            WHERE (p.is_agent = true AND p.is_deleted = false AND s.date_last > :cutoff) $or_id
            GROUP BY p.id
            ORDER BY s.date_last DESC
        ");

        if ($or_id) {
            $sessions_q->setParameter('person', App::getCurrentPerson());
        }

        $sessions = $sessions_q->setParameter('cutoff', $cutoff)->execute();

        $online_agents = [];
        foreach ($sessions as $s) {
            if ($ids_only) {
                $online_agents[$s->person['id']] = $s->person['id'];
            } else {
                $online_agents[$s->person['id']] = $s->person;
            }
        }

        if ($ids_only) {
            $sessions_q->free();
        }

        return $online_agents;
    }

    /**
     * @return array
     */
    public function getActiveAgentIdsForUserChat()
    {
        $datecut = date('Y-m-d H:i:s', time() - App::getSetting('core_chat.agent_timeout'));

        $agent_ids = $this->getEntityManager()->getConnection()->fetchAllCol("
            SELECT DISTINCT(sessions.person_id)
            FROM sessions
            LEFT JOIN people ON (people.id = sessions.person_id)
            WHERE
              sessions.date_last >= ?
              AND sessions.active_status = 'available'
              AND sessions.is_person = 1
              AND sessions.is_chat_available = 1
              AND people.is_agent = 1
        ", [$datecut]);

        return $agent_ids;
    }

    /**
     * Find a person by their email address.
     *
     * @param string $email
     * @param bool   $for_write
     *
     * @return PersonEntity|null
     */
    public function findOneByEmail($email, $for_write = false)
    {
        if (function_exists('mb_strtolower')) {
            $email = mb_strtolower($email);
        } else {
            $email = strtolower($email);
        }
        if (App::getDb()->isTransactionActive() && $for_write) {
            $person = $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Person p
                JOIN p.emails e
                WHERE e.email = ?1
                ORDER BY p.id ASC
            ')->setLockMode(LockMode::PESSIMISTIC_WRITE)->setParameter(1, $email)->setMaxResults(1)->getOneOrNullResult();
        } else {
            $person = $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Person p
                JOIN p.emails e
                WHERE e.email = ?1
                ORDER BY p.id ASC
            ')->setParameter(1, $email)->setMaxResults(1)->getOneOrNullResult();
        }

        return $person;
    }

    /**
     * @param array $emails
     *
     * @return PersonEntity[]
     */
    public function findByEmails(array $emails)
    {
        if (!$emails) {
            return [];
        }

        return $this->getEntityManager()->createQuery('
            SELECT p FROM DeskPRO:Person p
            JOIN p.emails e WITH e.email IN (:emails)
        ')->setParameter('emails', $emails)->getResult();
    }

    public function searchByEmailStartingWith($email, $limit = null)
    {
        $email = str_replace(['%', '_'], ['\\\\%', '\\\\_'], $email).'%';

        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p
            LEFT JOIN p.emails e
            WHERE e.email LIKE ?1
            ORDER BY p.id ASC
        ')->setParameter(1, $email)->setMaxResults($limit)->execute();
    }

    public function searchByEmail($email, $limit = null)
    {
        $email = '%'.str_replace(['%', '_'], ['\\\\%', '\\\\_'], $email).'%';

        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p
            LEFT JOIN p.emails e
            WHERE e.email LIKE ?1
            ORDER BY p.id ASC
        ')->setParameter(1, $email)->setMaxResults($limit)->execute();
    }

    public function getPeopleFromIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        $people = $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p INDEX BY p.id
            WHERE p.id IN(?0)
            ORDER BY p.id ASC
        ')->execute([array_values($ids)]);

        return $people;
    }

    public function getPeopleResultsFromIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        $people = $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p INDEX BY p.id
            WHERE p.id IN(?0)
            ORDER BY p.id ASC
        ')->execute([$ids]);

        return $people;
    }

    public function search($q, $limit = null)
    {
        $q = '%'.str_replace(['%', '_'], ['\\\\%', '\\\\_'], $q).'%';

        if (App::getSystemService('usersource_manager')->getUsersources()) {
            return $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Person p
                LEFT JOIN p.emails e
                LEFT JOIN p.usersource_assoc a
                WHERE
                    (p.name LIKE ?1)
                    OR (p.first_name LIKE ?2)
                    OR (p.last_name LIKE ?3)
                    OR (e.email LIKE ?4)
                    OR (a.identity_friendly LIKE ?5)
                ORDER BY p.date_last_login DESC, p.id DESC
            ')->setParameters([1 => $q, 2 => $q, 3 => $q, 4 => $q, 5 => $q])->setMaxResults($limit)->execute();
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT p
                FROM DeskPRO:Person p
                LEFT JOIN p.emails e
                WHERE (p.name LIKE ?1) OR (p.first_name LIKE ?2) OR (p.last_name LIKE ?3) OR (e.email LIKE ?4)
                ORDER BY p.date_last_login DESC, p.id DESC
            ')->setParameters([1 => $q, 2 => $q, 3 => $q, 4 => $q])->setMaxResults($limit)->execute();
        }
    }

    public function getOrganizationMembers(OrganizationEntity $org, $page = 1, $limit = 50)
    {
        $page = max(1, $page);

        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p INDEX BY p.id
            WHERE p.organization = ?1 AND p.is_deleted = false
            ORDER BY p.organization_manager DESC, p.last_name ASC, p.first_name ASC
        ')->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->execute([1 => $org]);
    }

    public function getOrganizationMemberIds(OrganizationEntity $org)
    {
        $ids     = [];
        $results = $this->getEntityManager()->createQuery('
            SELECT p.id
            FROM DeskPRO:Person p
            WHERE p.organization = ?1
        ')->execute([1 => $org]);
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }

        return $ids;
    }

    public function getUsergroupMembers(UsergroupEntity $ug)
    {
        return $this->getEntityManager()->createQuery('
            SELECT p
            FROM DeskPRO:Person p INDEX BY p.id
            LEFT JOIN p.usergroups ug
            WHERE ug.id = ?1
            ORDER BY p.last_name ASC, p.first_name ASC
        ')->setParameter(1, $ug)->execute();
    }

    public function getUsergroupMemberIds(UsergroupEntity $ug)
    {
        return $this->getEntityManager()->getConnection()->fetchAllCol('
            SELECT person_id
            FROM person2usergroups
            WHERE usergroup_id = ?
        ', [$ug->id]);
    }

    public function getChatAgentRoundRobin()
    {
        $active_agents_ids = App::getEntityRepository('DeskPRO:Session')->getAvailableAgentIds();

        // Count chats for each
        $chat_counts = App::getDb()->fetchAllKeyValue('
            SELECT agent_id, COUNT(*) as cnt
            FROM chat_conversations
            WHERE agent_id IS NOT NULL AND status = ?
            GROUP BY agent_id
            ORDER BY cnt DESC
        ', ['open']);

        foreach ($active_agents_ids as $id) {
            if (!isset($chat_counts[$id])) {
                $chat_counts[$id] = 0;
            }
        }

        $grouped = [];
        foreach ($chat_counts as $id => $cnt) {
            if (!isset($grouped[$cnt])) {
                $grouped[$cnt] = [];
            }
            $grouped[$cnt][] = $id;
        }

        asort($chat_counts, SORT_NUMERIC);
        $bottom_group = array_shift($chat_counts);

        // Only one person
        if (count($bottom_group) == 1) {
            return $this->find($bottom_group[0]);
        }

        // Otherwise, we'll fetch the person who hasnt had a chat in a while
        $id = $this->getEntityManager()->getConnection()->fetchColumn("
            SELECT agent_id
            FROM chat_conversations
            WHERE agent_id IN (?) AND status = 'ended'
            ORDER BY date_ended DESC
            LIMIT 1
        ", [$bottom_group], 0, [Connection::PARAM_INT_ARRAY]);

        return $this->find($id);
    }

    /**
     * Get a count of how many people there are.
     *
     * @param bool $only_users
     *
     * @return int
     */
    public function getCount($only_users = false)
    {
        $rows = App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM people
            WHERE people.is_deleted = 0
        ');

        if ($only_users) {
            return $rows - count($this->getAgents());
        } else {
            return $rows;
        }
    }

    /**
     * Count the number of things the user owns.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return array
     */
    public function getPersonObjectCounts(PersonEntity $person)
    {
        $pid = $person->getId();

        $counts = [
            'chats'   => $this->_em->getConnection()->fetchColumn('SELECT COUNT(*) FROM chat_conversations WHERE person_id = ?', [$pid]),
            'tickets' => $this->_em->getConnection()->fetchColumn('SELECT COUNT(*) FROM tickets WHERE person_id = ?', [$pid]),
        ];

        return $counts;
    }

    /**
     * moved from AgentBundle/Controller/PeopleSearchController::performQuickSearch.
     *
     * @param null $q          search query
     * @param bool $startWith  ?
     * @param bool $withAgents include agents
     * @param int  $excludeOrg exclude org
     * @param int  $limit      limit
     *
     * @return array
     */
    public function quickSearch($q = null, $startWith = false, $withAgents = true, $excludeOrg = 0, $limit = 10)
    {
        $startWith  = (bool) $startWith;
        $withAgents = (bool) $withAgents;
        $excludeOrg = abs($excludeOrg);
        $limit      = max(10, min($limit, 100));
        $agent_sql  = $withAgents ? '' : ' p.is_agent = 0 AND ';
        $db         = $this->getEntityManager()->getConnection();
        $q          = strtolower($q);

        if (BigMode::isBigMode(BigMode::PERSON_AUTOCOMPLETE)) {
            if (!strlen($q) && $startWith) {
                return $db->fetchAllKeyed("
                    SELECT p.id, p.first_name, p.last_name, p.name, e.email
                    FROM people p
                    LEFT JOIN people_emails e ON (e.id = p.primary_email_id)
                    WHERE $agent_sql
                    ".($excludeOrg ? " p.organization_id != $excludeOrg " : '1')."
                    ORDER BY p.id DESC
                    LIMIT $limit
                ");
            } else {
                return $db->fetchAllKeyed("
                    SELECT p.id, p.first_name, p.last_name, p.name, e.email
                    FROM people p
                    LEFT JOIN people_emails e ON (e.id = p.primary_email_id)
                    WHERE
                        $agent_sql
                        LOWER(e.email) LIKE ?
                        ".($excludeOrg ? " AND (p.organization_id IS NULL OR p.organization_id != $excludeOrg) " : '')."
                    GROUP BY p.id
                    ORDER BY p.date_last_login DESC, p.id DESC
                    LIMIT $limit
                ", ["$q%"]);
            }
        } else {
            if (!strlen($q) && $startWith) {
                return $db->fetchAllKeyed("
                    SELECT p.id, p.first_name, p.last_name, p.name, e.email
                    FROM people p
                    LEFT JOIN people_emails e ON (e.id = p.primary_email_id)
                    WHERE $agent_sql
                    ".($excludeOrg ? " p.organization_id != $excludeOrg " : '1')."
                    ORDER BY p.name ASC
                    LIMIT $limit
                ");
            } else {
                return $db->fetchAllKeyed("
                    SELECT p.id, p.first_name, p.last_name, p.name, e.email
                    FROM people p
                    LEFT JOIN people_emails e ON (e.id = p.primary_email_id)
                    WHERE
                        $agent_sql
                        (LOWER(e.email) LIKE ?
                        OR LOWER(p.name) LIKE ?
                        OR LOWER(p.first_name) LIKE ?
                        OR LOWER(p.last_name) LIKE ?)
                        ".($excludeOrg ? " AND (p.organization_id IS NULL OR p.organization_id != $excludeOrg) " : '')."
                    GROUP BY p.id
                    ORDER BY p.date_last_login DESC, p.id DESC
                    LIMIT $limit
                ", ["%$q%", "%$q%", "%$q%", "%$q%"]);
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getAgentsRaw()
    {
        $ret = [];

        // todo we don't need to hydrate entities here (by getAgents()), but before we should move all helpers outside of Person entity

        foreach ($this->getAgents() as $agent) {
            /* @var $agent \Application\DeskPRO\Entity\Person */
            $ret[] = [
                'id'           => $agent['id'],
                'display_name' => $agent->getDisplayNameUser(),
                'picture_url'  => $agent->getPictureUrl(16),
            ];
        }

        return $ret;
    }

    public function refresh(\Application\DeskPRO\Entity\Person $user)
    {
        $this->_em->refresh($user);

        return $user;
    }

    /**
     * @param string $phoneNumber
     *
     * @return PersonEntity|null
     */
    public function getOrCreateUserByPhoneNumber($phoneNumber)
    {
        $person = null;
        if ($phoneNumber) {
            // check for an existing person
            $phoneNumberEntity = $this->_em->getRepository(PhoneNumberEntity::class)->findOneBy([
                'number' => $phoneNumber,
            ]);
            if ($phoneNumberEntity) {
                $person = $phoneNumberEntity->getPerson();
            }

            // if person was not found then create a new one
            if (!$person) {
                $person = new PersonEntity();
                $person->setPrimaryPhoneNumber(PhoneNumberEntity::createEntity($phoneNumber));

                $this->_em->persist($person);
                $this->_em->flush();
            }
        }

        return $person;
    }
}
