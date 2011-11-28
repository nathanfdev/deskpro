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
		$wheres = array();
		$params = array();

		$wheres[] = "person_id = ?";
		$params[] = $person->id;

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

		$results = $this->getEntityManager()->getConnection()->fetchAllKeyValue("
			SELECT department_id, person_id
			FROM department_permissions
			WHERE department_id IN ($ids)
		");

		return $results;
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
