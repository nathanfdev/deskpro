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
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\DBAL\Connection;

class DepartmentEditor
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
	 * @return DepartmentEditor
	 */
	public function createNew(EntityManager $em)
	{
		$dep = new Department();
		$dep->is_tickets_enabled = true;
		return new self($em, $dep);
	}

	/**
	 * @param $em
	 * @param $dep
	 */
	public function __construct(EntityManager $em, Department $dep)
	{
		$this->em  = $em;
		$this->db  = $em->getConnection();
		$this->dep = $dep;
	}


	/**
	 * @param array $props
	 * @throws \InvalidArgumentException
	 */
	public function editProperties(array $props)
	{
		$move_to = null;

		if ($this->dep->parent && isset($props['parent_id']) && $props['parent_id'] == $this->dep->parent->getId()) {
			unset($props['parent_id']);
		}

		if (!$this->dep->parent && isset($props['parent_id'])) {
			if (!isset($props['move_tickets_to'])) {
				throw new \InvalidArgumentException("You must supply a move_tickets_to when moving top-level department to become a parent");
			}

			$move_to = $this->em->find('DeskPRO:Department', $props['move_tickets_to']);
			if (!$move_to) {
				throw new \InvalidArgumentException("move_tickets_to is an invalid department id");
			}
			if (!$move_to->is_tickets_enabled) {
				throw new \InvalidArgumentException("move_tickets_to is not a ticket department");
			}
			if (count($move_to->children)) {
				throw new \InvalidArgumentException("move_tickets_to cannot be a parent");
			}
			if ($move_to->id == $this->dep->id) {
				throw new \InvalidArgumentException("move_tickets_to cannot be itself");
			}
		}

		if (isset($props['title'])) {
			$this->dep->title = $props['title'];
		}
		if (isset($props['user_title'])) {
			$this->dep->title = $props['user_title'];
		}
		if (isset($props['parent_id'])) {
			$new_parent = $this->em->find('DeskPRO:Department', $props['parent_id']);
			if (!$new_parent) {
				throw new \InvalidArgumentException("parent_id does not exist");
			}
			if (!$new_parent->is_tickets_enabled) {
				throw new \InvalidArgumentException("parent_id is not a ticket department");
			}
			if ($new_parent->parent) {
				throw new \InvalidArgumentException("parent_id cannot be a child");
			}

			$this->dep->parent = $new_parent;

			if ($move_to) {
				// handle queued moving of tickets
			}
		}

		$this->em->persist($this->dep);
		$this->em->flush();
	}


	/**
	 * @param array $agent_perms
	 * @param array $agentgroup_perms
	 * @param array $usergroup_perms
	 */
	public function editPermissions(array $agent_perms = null, array $agentgroup_perms = null, array $usergroup_perms = null)
	{
		$inserts = array();

		if ($agentgroup_perms !== null) {
			$this->db->executeQuery("
				DELETE FROM department_permissions
				WHERE usergroup_id IS NOT NULL AND department_id = ?
			", array($this->dep->id));

			foreach ($agentgroup_perms as $perm) {
				$inserts[] = array(
					'department_id' => $this->dep->id,
					'usergroup_id' => $perm['usergroup_id'],
					'person_id' => null,
					'app' => 'tickets',
					'name' => $perm['perm_name'],
					'value' => 1,
				);
			}
		}

		if ($agent_perms !== null) {
			$this->db->executeQuery("
				DELETE FROM department_permissions
				WHERE person_id IS NOT NULL AND department_id = ?
			", array($this->dep->id));

			foreach ($agentgroup_perms as $perm) {
				$inserts[] = array(
					'department_id' => $this->dep->id,
					'usergroup_id' => null,
					'person_id' => $perm['person_id'],
					'app' => 'tickets',
					'name' => $perm['perm_name'],
					'value' => 1,
				);
			}
		}

		if ($usergroup_perms !== null) {
			$this->db->executeQuery("
				DELETE FROM department_permissions
				WHERE usergroup_id IS NOT NULL AND department_id = ?
			", array($this->dep->id));

			foreach ($usergroup_perms as $perm) {
				$inserts[] = array(
					'department_id' => $this->dep->id,
					'usergroup_id' => $perm['usergroup_id'],
					'person_id' => null,
					'app' => 'tickets',
					'name' => 'full',
					'value' => 1,
				);
			}
		}

		if ($inserts) {
			$this->db->batchInsert('department_permissions', $inserts);
		}
	}


	/**
	 * Deletes the department
	 */
	public function remove($move_to)
	{
		if (ctype_digit($move_to)) {
			$move_to = $this->em->find('DeskPRO:Department', $move_to);
		}

		if (!$move_to || $move_to->id == $this->dep->id) {
			throw new \InvalidArgumentException("You must specify a valid department to move existing tickets to");
		}

		$old_id = $this->dep->id;
		$this->em->remove($this->dep);
		$this->em->flush();
		return $old_id;
	}


	/**
	 * @return Department
	 */
	public function getDepartment()
	{
		return $this->dep;
	}
}