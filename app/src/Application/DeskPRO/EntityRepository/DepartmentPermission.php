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
use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Orb\Util\Arrays;

class DepartmentPermission extends AbstractEntityRepository
{
	/**
	 * array(agent_id => array(name => array(...), any => array(...))
	 * @var array
	 */
	private $cache_by_agent = null;

	/**
	 * array(group_id => array(name => array(...), any => array(...))
	 * @var null
	 */
	private $cache_by_group = null;

	public function getPermsForAgent($person_id, array $ug_ids, $name = null)
	{
		$ug_ids = Arrays::removeFalsey($ug_ids);
		if (!$ug_ids) {
			$ug_ids = array();
		}

		if ($this->cache_by_agent === null) {
			$this->cache_by_agent = array();
			$this->cache_by_group = array();

			$q = $this->_em->getConnection()->query("
				SELECT dp.app, dp.department_id, dp.usergroup_id, dp.person_id, dp.name, dp.value
				FROM department_permissions dp
			");

			while ($rec = $q->fetch()) {
				if ($rec['usergroup_id']) {
					if (!isset($this->cache_by_group[$rec['usergroup_id']])) {
						$this->cache_by_group[$rec['usergroup_id']]        = array();
						$this->cache_by_group[$rec['usergroup_id']]['ANY'] = array();
					}
					if (!isset($this->cache_by_group[$rec['usergroup_id']][$rec['name']])) {
						$this->cache_by_group[$rec['usergroup_id']][$rec['name']] = array();
					}

					$this->cache_by_group[$rec['usergroup_id']]['ANY'][]        = $rec;
					$this->cache_by_group[$rec['usergroup_id']][$rec['name']][] = $rec;
				} else if ($rec['person_id']) {
					if (!isset($this->cache_by_agent[$rec['person_id']])) {
						$this->cache_by_agent[$rec['person_id']]        = array();
						$this->cache_by_agent[$rec['person_id']]['ANY'] = array();
					}
					if (!isset($this->cache_by_agent[$rec['person_id']][$rec['name']])) {
						$this->cache_by_agent[$rec['person_id']][$rec['name']] = array();
					}

					$this->cache_by_agent[$rec['person_id']]['ANY'][]        = $rec;
					$this->cache_by_agent[$rec['person_id']][$rec['name']][] = $rec;
				}
			}
		}

		$found = array();

		if ($person_id && !empty($this->cache_by_agent[$person_id])) {
			if ($name) {
				$found = !empty($this->cache_by_agent[$person_id][$name]) ? $this->cache_by_agent[$person_id][$name] : array();
			} else {
				$found = !empty($this->cache_by_agent[$person_id]['ANY']) ? $this->cache_by_agent[$person_id]['ANY'] : array();
			}
		}

		if ($ug_ids) {
			foreach ($ug_ids as $ug_id) {
				if ($name) {
					$ug_found = !empty($this->cache_by_group[$ug_id][$name]) ? $this->cache_by_group[$ug_id][$name] : array();
				} else {
					$ug_found = !empty($this->cache_by_group[$ug_id]['ANY']) ? $this->cache_by_group[$ug_id]['ANY'] : array();
				}
			}

			if ($ug_found) {
				if ($found) {
					$found = array_merge($found, $ug_found);
				} else {
					$found = $ug_found;
				}
			}
		}

		return $found;
	}

	/**
	 * Get an array of department IDs this user has permission to see
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return int[]
	 */
	public function getDepartmentIdsForPerson(PersonEntity $person)
	{
		$wheres = array();
		$params = array();

		$wheres[] = "person_id = ?";
		$params[] = $person->id;

		$wheres[] = "name = 'full'";
		$wheres[] = "value = 1";

		$wheres = implode(' AND ', $wheres);
		$sql = "
			SELECT department_id
			FROM department_permissions
			WHERE $wheres
		";

		return $this->getEntityManager()->getConnection()->fetchAllCol($sql);
	}

	/**
	 * @return array
	 */
	public function getAllPersonPermissionsForAllDepartments($app, $name, $value)
	{
		return App::getDb()->fetchAllGrouped("
			SELECT department_id, person_id
			FROM department_permissions
			WHERE app = ? AND person_id IS NOT NULL
				AND name = ? AND value = ?
		", array($app, $name, $value), 'department_id', null, 'person_id');
	}

	/**
	 * @param DepartmentEntity $dep
	 * @param $app
	 * @return mixed
	 */
	public function getRecordsForDepartment(DepartmentEntity $dep, $app)
	{
		return $this->_em->createQuery("
			SELECT p
			FROM DeskPRO:DepartmentPermission p
			WHERE p.department = ?0 AND p.app = ?1
		")->execute(array($dep, $app));
	}
}
