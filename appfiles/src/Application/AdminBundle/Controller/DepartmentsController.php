<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

		$agents     = App::getEntityRepository('DeskPRO:Person')->getAgents();
		$teams      = App::getEntityRepository('DeskPRO:AgentTeam')->findAll();
		$usergroups = App::getEntityRepository('DeskPRO:Usergroup')->findAll();
		$current_options_tickets = App::getEntityRepository('DeskPRO:DepartmentPermission')->getAllPermissionsForAllDepartments('tickets');
		$current_options_chat = App::getEntityRepository('DeskPRO:DepartmentPermission')->getAllPermissionsForAllDepartments('chat');

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
		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		$app = $this->in->getString('app');

		App::getDb()->executeUpdate("
			DELETE
			FROM department_permissions
			WHERE department_id = ? AND app = ?
		", array($department_id, $app));

		$agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');

		if ($agent_ids) {
			App::getDb()->beginTransaction();

			if ($agent_ids) {
				foreach ($agent_ids as $agent_id) {
					App::getDb()->insert('department_permissions', array('department_id' => $department->id, 'person_id' => $agent_id, 'app' => $app));
				}
			}

			App::getDb()->commit();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function saveFeatureStateAction($department_id)
	{
		$chat = $this->in->getBool('chat');
		$tickets = $this->in->getBool('tickets');

		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		$department->is_tickets_enabled = $tickets;
		$department->is_chat_enabled= $chat;

		App::getOrm()->transactional(function($em) use ($department) {
			$em->persist($department);
		});

		return $this->createJsonResponse(array('success' => 1));
	}

	public function saveTitleAction()
	{
		$department_id = $this->in->getUint('department_id');
		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$department->title = $this->in->getString('title');
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

		if (!$department->title) {
			$department->title = 'Untitled';
		}

		if ($this->in->getUint('parent_id')) {
			$parent = App::findEntity('DeskPRO:Department', $this->in->getUint('parent_id'));
		}

		if ($parent and !$parent->parent) {
			$department->parent = $parent;
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

	############################################################################
	# delete
	############################################################################

	public function deleteAction($department_id)
	{
		$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);

		$tree_ids = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($department->id, true);
		$tree_ids = implode(',', $tree_ids);

		$ticket_count = App::getDb()->fetchColumn("
			SELECT COUNT(*) FROM tickets
			WHERE department_id IN ($tree_ids)
		");
		$chat_count = App::getDb()->fetchColumn("
			SELECT COUNT(*) FROM chat_conversations
			WHERE department_id IN ($tree_ids)
		");

		$departments = App::getEntityRepository('DeskPRO:Department')->getAll();

		return $this->render('AdminBundle:Departments:delete.html.twig', array(
			'department'  => $department,
			'ticket_count' => $ticket_count,
			'chat_count' => $chat_count,
			'departments' => $departments
		));
	}

	public function doDeleteAction($department_id, $security_token)
	{
		$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		$move_department = null;

		$tree_ids = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($department->id, true);
		$tree_ids = implode(',', $tree_ids);

		$ticket_count = App::getDb()->fetchColumn("
			SELECT COUNT(*) FROM tickets
			WHERE department_id IN ($tree_ids)
			LIMIT 1
		");
		$chat_count = App::getDb()->fetchColumn("
			SELECT COUNT(*) FROM chat_conversations
			WHERE department_id IN ($tree_ids)
			LIMIT 1
		");

		$has_tickets = ($ticket_count || $chat_count);

		if ($has_data) {
			$move_department = App::getEntityRepository('DeskPRO:Department')->find($this->in->getUint('move_to_department'));
			if (!$move_department || count($move_department->children)) {
				// TODO err
				die('invalid new department');
			}
		}

		if (!$this->session->getEntity()->checkSecurityToken('delete_department', $security_token)) {
			// TODO err
			die('invalid token');
		}

		$this->em->beginTransaction();

		if ($has_tickets) {
			App::getDb()->executeUpdate("
				UPDATE tickets
				SET department_id = ?
				WHERE department_id IN ($tree_ids)
			", array($move_department->id));

			App::getDb()->executeUpdate("
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
