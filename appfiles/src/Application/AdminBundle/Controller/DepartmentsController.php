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
		$this->rememberLastPage();

		$all_departments = $this->em->createQuery("
			SELECT dep
			FROM DeskPRO:Department dep
			WHERE dep.parent IS NULL
			ORDER BY dep.display_order ASC
		")->getResult();

		$agents     = App::getEntityRepository('DeskPRO:Person')->getAgents();
		$teams      = App::getEntityRepository('DeskPRO:AgentTeam')->findAll();
		$usergroups = App::getEntityRepository('DeskPRO:Usergroup')->findAll();
		$current_options = App::getEntityRepository('DeskPRO:DepartmentPermission')->getPermissionsForAllDepartments();

		return $this->render('AdminBundle:Departments:list.html.twig', array(
			'all_departments' => $all_departments,
			'agents' => $agents,
			'teams' => $teams,
			'usergroups' => $usergroups,
			'current_options' => $current_options,
		));
	}

	public function saveAgentsAction($department_id)
	{
		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		APp::getDb()->executeUpdate("
			DELETE
			FROM department_permissions
			WHERE apply_type IN ('agent_team', 'person')
			AND department_id = ?
		", array($department_id));

		$agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');
		$agent_team_ids = $this->in->getCleanValueArray('agent_team_ids', 'uint', 'discard');

		if ($agent_ids || $agent_team_ids) {
			App::getDb()->beginTransaction();

			if ($agent_ids) {
				foreach ($agent_ids as $agent_id) {
					App::getDb()->insert('department_permissions', array('apply_type' => 'person', 'department_id' => $department->id, 'person_id' => $agent_id));
				}
			}
			if ($agent_team_ids) {
				foreach ($agent_team_ids as $agent_team_id) {
					App::getDb()->insert('department_permissions', array('apply_type' => 'agent_team', 'department_id' => $department->id, 'agent_team_id' => $agent_team_id));
				}
			}

			App::getDb()->commit();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function saveUsergroupsAction($department_id)
	{
		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw $this->createNotFoundException();
		}

		APp::getDb()->executeUpdate("
			DELETE
			FROM department_permissions
			WHERE apply_type = 'usergroup'
			AND department_id = ?
		", array($department_id));

		$usergroup_ids = $this->in->getCleanValueArray('usergroup_ids', 'uint', 'discard');

		if ($usergroup_ids) {
			App::getDb()->beginTransaction();
			foreach ($usergroup_ids as $usergroup_id) {
				App::getDb()->insert('department_permissions', array('apply_type' => 'usergroup', 'department_id' => $department->id, 'usergroup_id' => $usergroup_id));
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

	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a department
	 */
	public function editAction($department_id)
	{
		if (!$department_id) {
			$department = new Entity\Department();
		} else {
			$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		}

		$form = $this->get('form.factory')->create(new EditDepartmentType($department->id ? false : true), $department);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($department);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $department->title);
				return $this->redirectRoute('admin_departments');
			}
		}



		return $this->render('AdminBundle:Departments:edit.html.twig', array(
			'department' => $department,
			'form'      => $form->createView(),
		));
	}

	############################################################################
	# designer
	############################################################################

	/**
	 * "Designs" a department: sets custom ticket rules etc
	 */
	public function designerAction($department_id)
	{
		$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		if ($this->in->getBool('process')) {
			$this->_saveDepartmentDesigner($department);
		}

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields_dummy');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $custom_fields_form);

		// widgets
		$widgets = App::getEntityRepository('DeskPRO:Widget')->getWidgetsForSection(array('agent.ticket', 'user.ticket'));

		// Existing rules:
		$display_elements = App::getOrm()->createQuery("
			SELECT d
			FROM DeskPRO:DepartmentTicketDisplay d
			WHERE d.department = ?1
		")->execute(array(1=>$department));

		return $this->render('AdminBundle:Departments:designer.html.twig', array(
			'department' => $department,
			'display_elements' => $display_elements,
			'custom_fields' => $custom_fields,
			'term_options' => $term_options,
			'widgets' => $widgets
		));
	}

	protected function _saveDepartmentDesigner(Entity\Department $department)
	{
		// The items we've added
		$display_items = $this->in->getCleanValueArray('display_items', 'string', 'discard');

		// Array of itemid=>initial display, 'visible' or 'hidden'
		$display_items_initial = $this->in->getCleanValueArray('initial_display', 'string', 'string');

		// Array of itemid=>terms array
		$display_items_terms_all = $this->in->getCleanValueArray('terms_all', 'array', 'string');
		$display_items_terms_any = $this->in->getCleanValueArray('terms_any', 'array', 'string');

		$display_items_agentonly = $this->in->getCleanValueArray('terms_any', 'bool', 'string');

		App::getOrm()->beginTransaction();

		// Clear out the previous rules, we'll just reset them all now
		App::getOrm()->createQuery("
			DELETE FROM DeskPRO:DepartmentTicketDisplay d
			WHERE d.department = ?1
		")->execute(array(1=>$department));
		App::getOrm()->flush();

		$order_count = 0;
		foreach ($display_items as $display_item_id) {

			$order_count += 10;

			if (strpos($display_item_id, '.') !== false) {
				list ($item_type, $item_id) = explode('.', $display_item_id, 2);
			} else {
				$item_type = $display_item_id;
				$item_id = 0;
			}

			$display = new Entity\DepartmentTicketDisplay();
			$display->department = $department;
			$display['element_type'] = $item_type;
			$display['element_id'] = $item_id;
			$display['display_order'] = $order_count;

			if (isset($display_items_agentonly[$display_item_id]) AND $display_items_agentonly[$display_item_id]) {
				$display['is_agent_only'] = true;
			}

			if (isset($display_items_initial[$display_item_id])) {
				$display['initial_state'] = ($display_items_initial[$display_item_id] == 'hidden' ? 'hidden' : 'visible');
			}

			if (isset($display_items_terms_all[$display_item_id]) AND $display_items_terms_all[$display_item_id]) {
				$display['conds_all'] = $display_items_terms_all[$display_item_id];
			}

			if (isset($display_items_terms_any[$display_item_id]) AND $display_items_terms_any[$display_item_id]) {
				$display['conds_any'] = $display_items_terms_any[$display_item_id];
			}

			App::getOrm()->persist($display);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();
	}

	public function designerAjaxFetchFieldAction($field_id)
	{
		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields_dummy');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $custom_fields_form);

		$html = '';
		if (isset($custom_fields[$field_id])) {
			$field = $custom_fields[$field_id];
			$html = $this->renderView('AdminBundle:Departments:designer-field-choicerow.html.twig', array('field' => $field));
		}

		return $this->createJsonResponse(array('html' => $html));
	}

	public function designerAjaxFetchWidgetAction($widget_id)
	{
		$widget = App::getEntityRepository('DeskPRO:Widget')->find($widget_id);
		$html = $this->renderView('AdminBundle:Departments:designer-widget-choicerow.html.twig', array('widget' => $widget));

		return $this->createJsonResponse(array('html' => $html));
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
