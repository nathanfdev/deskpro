<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Orb\Util\Arrays;

class Usergroup extends AbstractEntityRepository
{
    /** @var array|null */
    protected $_usergroup_names = null;
    /** @var array|null */
    protected $_agent_usergroup_names = null;

    /**
     * @return \Application\DeskPRO\Entity\Usergroup[]
     */
    public function getUserUsergroups()
    {
        return $this->_em->createQuery('
            SELECT ug
            FROM DeskPRO:Usergroup ug INDEX BY ug.id
            WHERE ug.is_agent_group = 0
            ORDER BY ug.title ASC
        ')->execute();
    }

    /**
     * @return \Application\DeskPRO\Entity\Usergroup[]
     */
    public function getAgentUsergroups()
    {
        return $this->_em->createQuery('
            SELECT ug
            FROM DeskPRO:Usergroup ug INDEX BY ug.id
            WHERE ug.is_agent_group = true
            ORDER BY ug.title ASC
        ')->execute();
    }

    /**
     * Get an array of id=>name for usergroups.
     *
     * @return array
     */
    public function getUsergroupNames($for_ids = null)
    {
        if ($this->_usergroup_names === null) {
            $db                     = $this->_em->getConnection();
            $this->_usergroup_names = $db->fetchAllKeyValue('
                SELECT id, title
                FROM usergroups
                WHERE is_agent_group = 0 AND sys_name IS NULL
                ORDER BY title ASC
            ');
        }

        if ($for_ids === null) {
            return $this->_usergroup_names;
        }

        $ret = [];
        if (in_array(1, $for_ids)) {
            $ret[1] = App::getTranslator()->phrase('agent.general.everyone');
        }
        if (in_array(2, $for_ids)) {
            $ret[2] = App::getTranslator()->phrase('agent.general.registered');
        }
        foreach ($for_ids as $id) {
            if (isset($this->_usergroup_names[$id])) {
                $ret[$id] = $this->_usergroup_names[$id];
            }
        }

        return $ret;
    }

    /**
     * Get an array of id=>name for agent usergroups.
     *
     * @return array
     */
    public function getAgentUsergroupNames()
    {
        if ($this->_agent_usergroup_names !== null) {
            return $this->_agent_usergroup_names;
        }
        $db                           = $this->getEntityManager()->getConnection();
        $this->_agent_usergroup_names = $db->fetchAllKeyValue('
            SELECT id, title
            FROM usergroups
            WHERE is_agent_group = 0 AND sys_name IS NULL
            ORDER BY title ASC
        ');

        return $this->_agent_usergroup_names;
    }

    public function getByIds(array $ids, $keep_order = false)
    {
        if (!$ids) {
            return [];
        }

        return $this->getEntityManager()->createQuery('
            SELECT u
            FROM DeskPRO:Usergroup u INDEX BY u.id
            WHERE u.id IN (?0)
            ORDER BY u.id DESC
        ')->execute([$ids]);
    }

    /**
     * get the counts for all usergroups.
     *
     * @return array
     */
    public function getCountsForAll()
    {
        /** @var Connection $conn */
        $conn   = $this->getEntityManager()->getConnection();
        $output = $conn->fetchAllKeyValue('
            SELECT usergroup_id, COUNT(*)
            FROM person2usergroups
            GROUP BY usergroup_id
        ');
        $output = array_map('intval', $output);

        $results = $conn->fetchAll('
            SELECT o2u.usergroup_id, (SELECT COUNT(*) FROM people WHERE people.organization_id = o2u.organization_id) AS total
            FROM organization2usergroups AS o2u
        ');
        foreach ($results as $result) {
            if (!$result['total']) {
                continue;
            }
            if (isset($output[$result['usergroup_id']])) {
                $output[$result['usergroup_id']] += $result['total'];
            } else {
                $output[$result['usergroup_id']] = $result['total'];
            }
        }

        return $output;
    }

    /**
     * Count the number of members in usergroups ($ids).
     *
     * @param array $ids
     *
     * @return array
     */
    public function getCountsFor(array $ids)
    {
        if (!$ids) {
            return [];
        }

        /** @var Connection $conn */
        $conn   = $this->getEntityManager()->getConnection();
        $output = $conn->fetchAllKeyValue('
            SELECT usergroup_id, COUNT(*)
            FROM person2usergroups
            WHERE usergroup_id IN (?)
            GROUP BY usergroup_id
        ', [$ids], [Connection::PARAM_INT_ARRAY]);
        $output = Arrays::castToType($output, 'int', 'int');

        // Org counts
        // Need to count all members of the org that are not part of the usergroup themselves
        $results = $conn->fetchAll('
            SELECT o2u.usergroup_id, COUNT(*) AS total
            FROM people
            LEFT JOIN organization2usergroups AS o2u ON (o2u.organization_id = people.organization_id)
            LEFT JOIN person2usergroups AS p2u ON (p2u.person_id = people.id AND p2u.usergroup_id = o2u.usergroup_id)
            WHERE o2u.usergroup_id IN (?) AND p2u.person_id IS NULL
            GROUP BY o2u.usergroup_id
        ', [$ids], [Connection::PARAM_INT_ARRAY]);

        if ($results) {
            foreach ($results as $result) {
                if (!$result['total']) {
                    continue;
                }
                if (isset($output[$result['usergroup_id']])) {
                    $output[$result['usergroup_id']] += $result['total'];
                } else {
                    $output[$result['usergroup_id']] = $result['total'];
                }
            }
            $output = Arrays::castToType($output, 'int', 'int');
        }

        return $output;
    }

    /**
     * Count the number of organization members in usergroups ($ids).
     *
     * @param array $ids
     *
     * @return int
     */
    public function getOrganizationCountsFor(array $ids)
    {
        if (!$ids) {
            return [];
        }

        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();

        return $conn->fetchAllKeyValue('
            SELECT usergroup_id, COUNT(*)
            FROM organization2usergroups
            WHERE usergroup_id IN (?)
            GROUP BY usergroup_id
        ', [$ids], [Connection::PARAM_INT_ARRAY]);
    }

    /**
     * Get all agents of all teams, and sort them into an array keyed
     * by team: array('teamid' => array('agentid', 'agentid')).
     *
     * @return array
     */
    public function getSortedAgentIds()
    {
        return $this->getEntityManager()->getConnection()->fetchAllGrouped('
            SELECT person2usergroups.usergroup_id, person2usergroups.person_id
            FROM person2usergroups
            LEFT JOIN usergroups ON usergroups.id = person2usergroups.usergroup_id
            WHERE usergroups.is_agent_group = 1
        ', [], 'usergroup_id', null, 'person_id');
    }
}
