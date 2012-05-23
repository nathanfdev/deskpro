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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditDepartmentType;
use Application\DeskPRO\Searcher\TicketSearch;

/**
 * Handles creating/editing of API keys
 */
class DepartmentsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of departments
	 */
	public function listAction()
	{
		$all_departments = $this->em->createQuery("
			SELECT dep
			FROM DeskPRO:Department dep
			WHERE dep.parent IS NULL
			ORDER BY dep.display_order ASC
		")->getResult();

		$agents     = $this->em->getRepository('DeskPRO:Person')->getAgents();
		$teams      = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();
		$usergroups = $this->em->getRepository('DeskPRO:Usergroup')->findAll();
		$current_options_tickets = $this->em->getRepository('DeskPRO:DepartmentPermission')->getAllPersonPermissionsForAllDepartments('tickets');
		$current_options_chat = $this->em->getRepository('DeskPRO:DepartmentPermission')->getAllPersonPermissionsForAllDepartments('chat');

		return $this->render('AdminBundle:Departments:list.html.twig', array(
			'all_departments' => $all_departments,
			'agents' => $agents,
			'teams' => $teams,
			'usergroups' => $usergroups,
			'current_options_tickets' => $current_options_tickets,
			'current_options_chat' => $current_options_chat,
		));
	}

	public function saveAgentsAction($department_id)
	{
		$department = $this->em->find('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		$app = $this->in->getString('app');

		$this->db->executeUpdate("
			DELETE
			FROM department_permissions
			WHERE department_id = ? AND app = ? AND person_id IS NOT NULL
		", array($department_id, $app));

		$agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');

		if ($agent_ids) {
			$this->db->beginTransaction();

			if ($agent_ids) {
				foreach ($agent_ids as $agent_id) {
					$this->db->insert('department_permissions', array('department_id' => $department->id, 'person_id' => $agent_id, 'app' => $app));
				}
			}

			$this->db->commit();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function saveFeatureStateAction($department_id)
	{
		$chat = $this->in->getBool('chat');
		$tickets = $this->in->getBool('tickets');

		$department = $this->em->find('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		$department->is_tickets_enabled = $tickets;
		$department->is_chat_enabled= $chat;

		$this->em->transactional(function($em) use ($department) {
			$em->persist($department);
		});

		return $this->createJsonResponse(array('success' => 1));
	}

	public function saveTitleAction()
	{
		$department_id = $this->in->getUint('department_id');
		$department = $this->em->find('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$department->title = $this->in->getString('title');
		}

		$department->user_title = $this->in->getString('user_title');

		$parent_id = $this->in->getUint('parent_id');
		if (!count($department->getChildren())) {
			if (!$parent_id || $parent_id == $department->getId()) {
				$department->parent = null;
			} else {
				$parent_dep = $this->em->find('DeskPRO:Department', $parent_id);
				if ($parent_dep && !count($parent_dep->parent)) {
					$department->parent = $parent_dep;
				}
			}
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($department);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_departments');
	}

	public function saveNewAction()
	{
		$department = new \Application\DeskPRO\Entity\Department();
		$department->title = $this->in->getString('title');
		$department->user_title = $this->in->getString('user_title');

		if (!$department->title) {
			$department->title = 'Untitled';
		}

		$parent = null;
		if ($this->in->getUint('parent_id')) {
			$parent = $this->em->find('DeskPRO:Department', $this->in->getUint('parent_id'));
		}

		if ($parent and !$parent->parent) {
			$department->parent = $parent;
		}

		$agent_ids = $this->db->fetchAllCol("
			SELECT id FROM people WHERE is_agent = 1
		");

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($department);
			$this->em->flush();

			$dep_perms = array();
			foreach ($agent_ids as $aid) {
				$dep_perms[] = array(
					'department_id' => $department->getId(),
					'usergroup_id' => null,
					'person_id' => $aid,
					'app' => 'tickets'
				);
				$dep_perms[] = array(
					'department_id' => $department->getId(),
					'usergroup_id' => null,
					'person_id' => $aid,
					'app' => 'chat'
				);
			}
			$dep_perms[] = array(
				'department_id' => $department->getId(),
				'usergroup_id' => 1,
				'person_id' => null,
				'app' => 'tickets'
			);
			$dep_perms[] = array(
				'department_id' => $department->getId(),
				'usergroup_id' => 1,
				'person_id' => null,
				'app' => 'chat'
			);

			$this->db->batchInsert('department_permissions', $dep_perms);

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_departments');
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($department_id)
	{
		$department = $this->em->getRepository('DeskPRO:Department')->find($department_id);

		$tree_ids = $this->em->getRepository('DeskPRO:Department')->getIdsInTree($department->id, true);
		$tree_ids = implode(',', $tree_ids);

		$ticket_count = $this->db->fetchColumn("
			SELECT COUNT(*) FROM tickets
			WHERE department_id IN ($tree_ids)
		");
		$chat_count = $this->db->fetchColumn("
			SELECT COUNT(*) FROM chat_conversations
			WHERE department_id IN ($tree_ids)
		");

		$departments = $this->container->getDataService('Department')->getAll();

		return $this->render('AdminBundle:Departments:delete.html.twig', array(
			'department'  => $department,
			'ticket_count' => $ticket_count,
			'chat_count' => $chat_count,
			'departments' => $departments
		));
	}

	public function doDeleteAction($department_id, $security_token)
	{
		$department = $this->em->getRepository('DeskPRO:Department')->find($department_id);
		$move_department = null;

		$tree_ids = $this->em->getRepository('DeskPRO:Department')->getIdsInTree($department->id, true);
		$tree_ids = implode(',', $tree_ids);

		$ticket_count = $this->db->fetchColumn("
			SELECT COUNT(*) FROM tickets
			WHERE department_id IN ($tree_ids)
			LIMIT 1
		");
		$chat_count = $this->db->fetchColumn("
			SELECT COUNT(*) FROM chat_conversations
			WHERE department_id IN ($tree_ids)
			LIMIT 1
		");

		$has_data = ($ticket_count || $chat_count);

		if ($has_data) {
			$move_department = $this->em->getRepository('DeskPRO:Department')->find($this->in->getUint('move_to_department'));
			if (!$move_department) {
				return $this->renderStandardError('You need to choose a department to move existing data into.');
			} elseif (count($move_department->children)) {
				return $this->renderStandardError('You chose an invalid department to move existing data into. The new department cannot have children.');
			}
		}

		if (!$this->session->getEntity()->checkSecurityToken('delete_department', $security_token)) {
			return $this->renderStandardTokenError();
		}

		$this->em->beginTransaction();

		if ($has_data) {
			$this->db->executeUpdate("
				UPDATE tickets
				SET department_id = ?
				WHERE department_id IN ($tree_ids)
			", array($move_department->id));

			$this->db->executeUpdate("
				UPDATE chat_conversations
				SET department_id = ?
				WHERE department_id IN ($tree_ids)
			", array($move_department->id));
		}

		foreach ($department->children as $c) {
			$this->em->remove($c);
		}
		$this->em->remove($department);
		$this->em->flush();
		$this->em->commit();

		$this->session->setFlash('deleted', $department->title);
		return $this->redirectRoute('admin_departments');
	}

	############################################################################
	# update-orders
	############################################################################

	public function updateOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('departments');
	}
}
