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
	 * @return array
	 */
	public function getAllPermissionsForAllDepartments($app)
	{
		return App::getDb()->fetchAllGrouped("
			SELECT department_id, person_id
			FROM department_permissions
			WHERE app = ?
		", array($app), 'department_id', null, 'person_id');
	}
}
