<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\Department as DepartmentEntity;
use Application\DeskPRO\Entity\DepartmentPermission as DepartmentPermissionEntity;
use Orb\Util\Numbers;

class DepartmentPermission extends EntityRepository
{
	/**
	 * Get an array of department IDs this user has permission to see
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return int[]
	 */
	public function getDepartmentIdsForPerson(PersonEntity $person)
	{
		$agent_team_ids = array();
		$usergroup_ids  = $person->getUsergroupIds();

		if ($person->is_agent) {
			$person->loadHelper('AgentTeam');
			$agent_team_ids = $person->getAgent()->getTeamIds();
		}

		$wheres = array();
		$params = array();

		$wheres[] = "person_id = ?";
		$params[] = DepartmentPermissionEntity::TYPE_PERSON;
		$params[] = $person->id;

		if ($agent_team_ids) {
			$agent_team_ids = implode(',', $agent_team_ids);
			$wheres[] = "agent_team_id IN($agent_team_ids)";
			$params[] = DepartmentPermissionEntity::TYPE_AGENT_TEAM;
		}

		if ($usergroup_ids) {
			$usergroup_ids = implode(',', $agent_team_ids);
			$wheres[] = "usergroup_id IN($usergroup_ids)";
			$params[] = DepartmentPermissionEntity::TYPE_USERGROUP;
		}

		$wheres = implode(' AND ', $wheres);
		$sql = "
			SELECT department_id
			FROM department_permissions
			WHERE $wheres
		";

		return $this->getEntityManager()->getConnection()->fetchAllCol($sql);
	}


	/**
	 * Get an array of id's for `agent_team` and `usergroup` types.
	 *
	 * Returns array('agent_team' => array(1,2,3), 'usergroup' => array(1,2,3))
	 *
	 * @param \Application\DeskPRO\Entity\Department $department
	 * @return array
	 */
	public function getPermissionsForDepartment(DepartmentEntity $department, $inc_child = true)
	{
		$ids = array();
		if ($inc_child) {
			foreach ($department->getChildren() as $c) {
				$ids[] = $c->id;
			}
		}

		$ids = implode(',', $ids);

		$results = $this->getEntityManager()->getConnection()->fetchAllGrouped("
			SELECT apply_type, COALESCE(usergroup_id, agent_team_id, person_id) AS apply_who
			FROM department_permissions
			WHERE
				department_id IN ($ids);
		", array(), 'apply_type', null, 'apply_who');

		return $results;
	}


	/**
	 * Get an array of id's for `agent_team` and `usergroup` types.
	 *
	 * Returns array(
	 *   1 => array('agent_team' => array(1,2,3), 'usergroup' => array(1,2,3))),
	 * )
	 *
	 * @param \Application\DeskPRO\Entity\Department[] $departments
	 * @return array
	 */
	public function getPermissionsForDepartments(array $departments, $inc_child = true)
	{
		$ids = array();
		$fetch_children = array();

		foreach ($departments as $d) {
			if ($d instanceof DepartmentEntity) {
				$ids[] = $d->id;
				if ($inc_child) {
					$fetch_children[] = $d->id;
				}
			} elseif (Numbers::isInteger($d)) {
				$ids[] = $d;
				if ($inc_child) {
					if ($inc_child) {
						$fetch_children[] = $d;
					}
				}
			}
		}

		if (!$ids) {
			return array();
		}

		if ($fetch_children) {
			foreach ($fetch_children as $d) {
				$ids = array_merge(
					$ids,
					$this->getEntityManager()->getRepository('DeskPRO:Department')->getIdsInTree($d, false)
				);
			}
		}

		$ids = implode(',', $ids);

		$results = $this->getEntityManager()->getConnection()->fetchAll("
			SELECT department_id, apply_type, COALESCE(usergroup_id, agent_team_id, person_id) AS apply_who
			FROM department_permissions
			WHERE
				department_id IN ($ids);
		");

		$return_results = array();

		foreach ($results as $r) {
			if (!isset($return_results[$r['department_id']])) {
				$return_results[$r['department_id']] = array();
			}

			if (!isset($return_results[$r['department_id']][$r['apply_type']])) {
				$return_results[$r['department_id']][$r['apply_type']] = array();
			}

			$return_results[$r['department_id']][$r['apply_type']][] = $r['apply_who'];
		}

		return $return_results;
	}


	/**
	 * @return array
	 */
	public function getPermissionsForAllDepartments()
	{
		$dep_ids = $this->getEntityManager()->getRepository('DeskPRO:Department')->getDepartmentIds();
		return $this->getPermissionsForDepartments($dep_ids, true);
	}
}
