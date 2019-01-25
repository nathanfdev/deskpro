<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Orb\Util\Arrays;

class DepartmentPermission extends AbstractEntityRepository
{
    /**
     * array(agent_id => array(name => array(...), any => array(...)).
     *
     * @var array
     */
    private $cache_by_agent = null;

    /**
     * array(group_id => array(name => array(...), any => array(...)).
     *
     * @var null
     */
    private $cache_by_group = null;

    public function getPermsForAgent($person_id, array $ug_ids, $name = null)
    {
        $ug_ids = Arrays::removeFalsey($ug_ids);
        if (!$ug_ids) {
            $ug_ids = [];
        }

        if ($this->cache_by_agent === null) {
            $this->cache_by_agent = [];
            $this->cache_by_group = [];

            $q = App::getContainer()->getDbRead()->query('
                SELECT dp.app, dp.department_id, dp.usergroup_id, dp.person_id, dp.name, dp.value
                FROM department_permissions dp
                WHERE is_active = 1
            ');

            while ($rec = $q->fetch()) {
                if ($rec['usergroup_id']) {
                    if (!isset($this->cache_by_group[$rec['usergroup_id']])) {
                        $this->cache_by_group[$rec['usergroup_id']]        = [];
                        $this->cache_by_group[$rec['usergroup_id']]['ANY'] = [];
                    }
                    if (!isset($this->cache_by_group[$rec['usergroup_id']][$rec['name']])) {
                        $this->cache_by_group[$rec['usergroup_id']][$rec['name']] = [];
                    }

                    $this->cache_by_group[$rec['usergroup_id']]['ANY'][]        = $rec;
                    $this->cache_by_group[$rec['usergroup_id']][$rec['name']][] = $rec;
                } elseif ($rec['person_id']) {
                    if (!isset($this->cache_by_agent[$rec['person_id']])) {
                        $this->cache_by_agent[$rec['person_id']]        = [];
                        $this->cache_by_agent[$rec['person_id']]['ANY'] = [];
                    }
                    if (!isset($this->cache_by_agent[$rec['person_id']][$rec['name']])) {
                        $this->cache_by_agent[$rec['person_id']][$rec['name']] = [];
                    }

                    $this->cache_by_agent[$rec['person_id']]['ANY'][]        = $rec;
                    $this->cache_by_agent[$rec['person_id']][$rec['name']][] = $rec;
                }
            }
        }

        $found = [];

        if ($person_id && !empty($this->cache_by_agent[$person_id])) {
            if ($name) {
                $found = !empty($this->cache_by_agent[$person_id][$name]) ? $this->cache_by_agent[$person_id][$name] : [];
                if ($name !== 'full' && !empty($this->cache_by_agent[$person_id]['full'])) {
                    $found = array_merge($found, $this->cache_by_agent[$person_id]['full']);
                }
            } else {
                $found = !empty($this->cache_by_agent[$person_id]['ANY']) ? $this->cache_by_agent[$person_id]['ANY'] : [];
            }
        }

        if ($ug_ids) {
            foreach ($ug_ids as $ug_id) {
                if ($name) {
                    $ug_found = !empty($this->cache_by_group[$ug_id][$name]) ? $this->cache_by_group[$ug_id][$name] : [];
                    if ($name !== 'full' && !empty($this->cache_by_group[$ug_id]['full'])) {
                        $ug_found = array_merge($ug_found, $this->cache_by_group[$ug_id]['full']);
                    }
                } else {
                    $ug_found = !empty($this->cache_by_group[$ug_id]['ANY']) ? $this->cache_by_group[$ug_id]['ANY'] : [];
                }

                if ($ug_found) {
                    if ($found) {
                        $found = array_merge($found, $ug_found);
                    } else {
                        $found = $ug_found;
                    }
                }
            }
        }

        return $found;
    }

    /**
     * Get an array of department IDs this user has permission to see.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return int[]
     */
    public function getDepartmentIdsForPerson(PersonEntity $person)
    {
        $wheres = [];
        $params = [];

        $wheres[] = 'person_id = ?';
        $params[] = $person->id;

        $wheres[] = "name = 'full'";
        $wheres[] = 'value = 1';
        $wheres[] = 'is_active = 1';

        $wheres = implode(' AND ', $wheres);
        $sql    = "
            SELECT department_id
            FROM department_permissions
            WHERE $wheres
        ";

        return $this->getEntityManager()->getConnection()->fetchAllCol($sql, $params);
    }

    /**
     * @return array
     */
    public function getAllPersonPermissionsForAllDepartments($app, $name, $value)
    {
        return App::getDb()->fetchAllGrouped('
            SELECT department_permissions.department_id, department_permissions.person_id
            FROM department_permissions
            WHERE department_permissions.app = ? 
              AND department_permissions.person_id IS NOT NULL
              AND department_permissions.is_active = 1
              AND department_permissions.name = ? 
              AND department_permissions.value = ?
        ', [$app, $name, $value], 'department_id', null, 'person_id');
    }

    /**
     * @param DepartmentEntity $dep
     * @param $app
     *
     * @return mixed
     */
    public function getRecordsForDepartment(DepartmentEntity $dep, $app)
    {
        return $this->_em->createQuery('
            SELECT p
            FROM DeskPRO:DepartmentPermission p
            WHERE p.department = ?0 AND p.app = ?1
        ')->execute([$dep, $app]);
    }
}
