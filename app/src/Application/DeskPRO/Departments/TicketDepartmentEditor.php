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
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\Exception\ValidationException;
use Doctrine\ORM\EntityManager;

class TicketDepartmentEditor
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
	 * @param $dep
	 */
	public function __construct(EntityManager $em)
	{
		$this->em       = $em;
		$this->db       = $em->getConnection();
	}


	/**
	 * @param int $id
	 * @return null|object
	 */
	public function getDepartmentById($id)
	{
		$dep = $this->em->find('DeskPRO:Department', $id);
		if (!$dep || !$dep->is_tickets_enabled) {
			return null;
		}

		return $dep;
	}


	/**
	 * @return Department
	 */
	public function createNewDepartment()
	{
		$dep = new Department();
		$dep->is_tickets_enabled = true;
		return $dep;
	}


	/**
	 * @param array $props
	 * @throws \InvalidArgumentException
	 */
	public function editProperties(Department $dep, array $props)
	{
		$move_to = null;
		$set_parent = null;
		$change_parent = false;

		if (isset($props['parent_id']) && $props['parent_id'] != $dep->getParentId()) {
			$change_parent = true;
			if ($props['parent_id']) {
				$set_parent = $this->em->find('DeskPRO:Department', $props['parent_id']);
				if (!$set_parent || !$set_parent->is_tickets_enabled) {
					throw ValidationException::create("department.parent_id.invalid", "parent_id does not exist");
				}

				if (!count($set_parent->children)) {
					if (!isset($props['move_tickets_to'])) {
						throw ValidationException::create("department.parent_id.move_tickets", "move_tickets_to required when moving to a top-level department");
					}
					if ($props['move_tickets_to'] == 'self') {
						$move_to = $dep;
					} else {
						$move_to = $this->em->find('DeskPRO:Department', $props['move_tickets_to']);
						if (!$move_to || !$move_to->is_tickets_enabled) {
							throw ValidationException::create("department.parent_id.move_tickets", "move_tickets_to is not a valid department");
						}

						if (count($move_to->children)) {
							throw ValidationException::create("department.parent_id.move_tickets", "move_tickets_to cannot be a parent");
						}
					}
				}
			} else {
				$set_parent = null;
			}
		}

		if (isset($props['title'])) {
			if (!$props['title']) {
				throw ValidationException::create("department.title.required");
			}
			$dep->title = $props['title'];
		}
		if (isset($props['user_title'])) {
			$dep->user_title = $props['user_title'];
		}
		if ($change_parent) {
			$dep->parent = $set_parent;
		}

		if (!$dep->getId()) {
			// Put new departments at the end
			$last = $this->db->fetchColumn("SELECT display_order FROM departments ORDER BY display_order DESC");
			$last += 10;

			$dep->display_order = $last;
		}

		$this->em->persist($dep);

		if ($change_parent && $move_to) {
			$this->db->update('tickets', array('department_id' => $move_to->getId()), array('department_id' => $set_parent->getId()));
			$this->db->update('tickets_search_active', array('department_id' => $move_to->getId()), array('department_id' => $set_parent->getId()));
		}
	}


	/**
	 * @param array $agent_perms
	 * @param array $agentgroup_perms
	 * @param array $usergroup_perms
	 */
	public function editPermissions(Department $dep, array $agent_perms = null, array $agentgroup_perms = null, array $usergroup_perms = null)
	{
		#------------------------------
		# Get curent perms
		#------------------------------

		$exist_agent_perms      = array('full' => array(), 'assign' => array());
		$exist_agentgroup_perms = array('full' => array(), 'assign' => array());
		$exist_usergroup_perms  = array('use'  => array());

		if ($dep->getId()) {
			$current_permissions = $this->em->createQuery("
				SELECT perm
				FROM DeskPRO:DepartmentPermission perm
				LEFT JOIN perm.usergroup ug
				LEFT JOIN perm.person p
				WHERE perm.department = ?0
			")->setParameters(array($dep))->execute();

			foreach ($current_permissions as $perm) {
				if ($perm->person) {
					$exist_agent_perms[$perm->name][$perm->person->getId()] = $perm;
				} else if ($perm->usergroup) {
					if ($perm->usergroup->is_agent_group) {
						$exist_agentgroup_perms[$perm->name][$perm->usergroup->getId()] = $perm;
					} else {
						$exist_usergroup_perms[$perm->name][$perm->usergroup->getId()] = $perm;
					}
				}
			}
		}

		#------------------------------
		# Get passed perms
		#------------------------------

		$set_agent_perms      = array('full' => array(), 'assign' => array());
		$set_agentgroup_perms = array('full' => array(), 'assign' => array());
		$set_usergroup_perms  = array('use'  => array());

		if ($agent_perms !== null) {
			foreach ($agent_perms as $perm) {
				$set_agent_perms[$perm['perm_name']][] = $perm['agent_id'];
			}
		}

		if ($agentgroup_perms !== null) {
			foreach ($agentgroup_perms as $perm) {
				$set_agentgroup_perms[$perm['perm_name']][] = $perm['usergroup_id'];
			}
		}

		if ($usergroup_perms !== null) {
			foreach ($usergroup_perms as $perm) {
				$set_usergroup_perms[$perm['perm_name']][] = $perm['usergroup_id'];
			}
		}

		#------------------------------
		# Update agent perms
		#------------------------------

		if ($agent_perms !== null) {
			foreach (array('full', 'assign') as $perm_name) {
				$new_ids = array_diff($set_agent_perms[$perm_name], array_keys($exist_agent_perms[$perm_name]));
				$rem_ids = array_diff(array_keys($exist_agent_perms[$perm_name]), $set_agent_perms[$perm_name]);

				if ($new_ids) {
					$people = $this->em->getRepository('DeskPRO:Person')->getByIds($new_ids);
					foreach ($people as $person) {
						$perm = new DepartmentPermission();
						$perm->department = $dep;
						$perm->person     = $person;
						$perm->app        = 'tickets';
						$perm->name       =  $perm_name;
						$perm->value      = true;

						$this->em->persist($perm);
					}
				}

				if ($rem_ids) {
					foreach ($rem_ids as $person_id) {
						$perm = $exist_agent_perms[$perm_name][$person_id];
						$this->em->remove($perm);
					}
				}
			}
		}

		#------------------------------
		# Update agent group perms
		#------------------------------

		if ($agentgroup_perms !== null) {
			foreach (array('full', 'assign') as $perm_name) {
				$new_ids = array_diff($set_agentgroup_perms[$perm_name], array_keys($exist_agentgroup_perms[$perm_name]));
				$rem_ids = array_diff(array_keys($exist_agentgroup_perms[$perm_name]), $set_agentgroup_perms[$perm_name]);

				if ($new_ids) {
					$groups = $this->em->getRepository('DeskPRO:Usergroup')->getByIds($new_ids);
					foreach ($groups as $group) {
						$perm = new DepartmentPermission();
						$perm->department = $dep;
						$perm->usergroup  = $group;
						$perm->app        = 'tickets';
						$perm->name       =  $perm_name;
						$perm->value      = true;

						$this->em->persist($perm);
					}
				}

				if ($rem_ids) {
					foreach ($rem_ids as $group_id) {
						$perm = $exist_agentgroup_perms[$perm_name][$group_id];
						$this->em->remove($perm);
					}
				}
			}
		}

		#------------------------------
		# Update user group perms
		#------------------------------

		if ($usergroup_perms !== null) {
			foreach (array('use') as $perm_name) {
				$new_ids = array_diff($set_usergroup_perms[$perm_name], array_keys($exist_usergroup_perms[$perm_name]));
				$rem_ids = array_diff(array_keys($exist_usergroup_perms[$perm_name]), $set_usergroup_perms[$perm_name]);

				if ($new_ids) {
					$groups = $this->em->getRepository('DeskPRO:Usergroup')->getByIds($new_ids);
					foreach ($groups as $group) {
						$perm = new DepartmentPermission();
						$perm->department = $dep;
						$perm->usergroup  = $group;
						$perm->app        = 'tickets';
						$perm->name       =  $perm_name;
						$perm->value      = true;

						$this->em->persist($perm);
					}
				}

				if ($rem_ids) {
					foreach ($rem_ids as $group_id) {
						$perm = $exist_usergroup_perms[$perm_name][$group_id];
						$this->em->remove($perm);
					}
				}
			}
		}
	}


	/**
	 * Deletes the department
	 */
	public function remove(Department $dep, $move_to_id)
	{
		$move_to = $this->em->find('DeskPRO:Department', $move_to_id);

		if (!$move_to) {
			throw ValidationException::create("department.remove.move_tickets", "You must specify a department to move to");
		}

		if ($move_to->id == $dep->id) {
			throw ValidationException::create("department.remove.move_tickets", "You must choose a different department");
		}

		if (count($move_to->getChildren())) {
			throw ValidationException::create("department.remove.move_tickets", "Department cannot be a parent");
		}

		$old_id = $dep->id;
		$this->em->remove($dep);
		$this->em->flush();

		$this->db->executeUpdate("UPDATE tickets SET department_id = ? WHERE department_id = ?", array($move_to, $old_id));
		$this->db->executeUpdate("UPDATE tickets_search_active SET department_id = ? WHERE department_id = ?", array($move_to, $old_id));

		return $old_id;
	}


	/**
	 * @param array $orders
	 */
	public function updateDisplayOrders($orders)
	{
		$x = 10;
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
	}
}