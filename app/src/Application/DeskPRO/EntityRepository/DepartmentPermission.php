<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

class DepartmentPermission extends AbstractEntityRepository
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

		$wheres[] = "name = 'full'";
		$wheres[] = "value = 1";

		$wheres = implode(' AND ', $wheres);
		$sql = "
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
