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
 */

namespace Application\DeskPRO\Departments;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;
use Doctrine\ORM\EntityManager;

class ChatDepartmentEditor
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */

	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */

	protected $db;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 */

	protected $dep;

	/**
	 * @param $em
	 */

	public function __construct(EntityManager $em)
	{
		$this->em       = $em;
		$this->db       = $em->getConnection();
	}

	/**
	 * @param array $orders
	 */

	public function updateDisplayOrders($orders)
	{
		$x    = 10;
		$deps = $this->em->getRepository('DeskPRO:Department')->getByIds($orders);

		foreach ($orders as $dep_id) {

			if (!isset($deps[$dep_id])) {

				continue;
			}

			$dep = $deps[$dep_id];

			$dep->display_order = $x;
			$this->em->persist($dep);

			$x += 10;
		}

		$this->em->flush();
	}

	/**
	 * Deletes the department
	 */

	public function removeDepartment(Department $dep, Department $move_to_dep)
	{
		if ($move_to_dep->id == $dep->id) {

			throw ValidationException::create("department.move_chat.deps_are_same");
		}

		if (count($move_to_dep->getChildren())) {

			throw ValidationException::create("department.move_chat.dep_is_parent");
		}

		$old_id = $dep->id;
		$new_id = $move_to_dep->id;

		$this->db->beginTransaction();

		try {

			$this->db->executeUpdate(
				"UPDATE chat_conversations SET department_id = ? WHERE department_id = ?",
				array($new_id, $old_id)
			);

			$this->em->remove($dep);
			$this->em->flush();

			$this->db->commit();

		} catch(\Exception $e) {

			$this->db->rollback();
			throw $e;
		}

		return $old_id;
	}
}