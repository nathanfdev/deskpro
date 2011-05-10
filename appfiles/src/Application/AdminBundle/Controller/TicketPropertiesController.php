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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\AdminBundle\Form\EditTicketPriorityType;

/**
 * Lists fields, categories, priorities, widgets, workflows
 */
class TicketPropertiesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of priorities
	 */
	public function listAction()
	{
		$this->rememberLastPage();

		/*
		$tabs = array(
			'categories'    => $this->forward('AdminBundle:TicketCategories:list')->getContent(),
			'priorities'    => $this->forward('AdminBundle:TicketPriorities:list')->getContent(),
			'workflows'     => $this->forward('AdminBundle:TicketWorkflows:list')->getContent(),
			'custom_def'    => $this->forward('AdminBundle:CustomDefTickets:index')->getContent(),
		);
		*/

		$counts = array();
		$counts['ticket_category'] = App::getEntityRepository('DeskPRO:TicketCategory')->countAll();
		$counts['ticket_priority'] = App::getEntityRepository('DeskPRO:TicketPriority')->countAll();
		$counts['ticket_workflow'] = App::getEntityRepository('DeskPRO:TicketWorkflow')->countAll();

		$fields = App::getApi('custom_fields.tickets')->getFields();

		return $this->render('AdminBundle:TicketProperties:list.html.twig', array(
			'counts' => $counts,
			'fields' => $fields
		));
	}

	############################################################################
	# editor
	############################################################################

	/**
	 * Shows the editor
	 */
	public function editorAction($department_id)
	{
		$departments = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();

		if (!$department_id) {
			$department_id = \Orb\Util\Arrays::getFirstItem($departments);
			$department_id = $department_id['id'];

			return $this->redirectRoute('admin_tickets_editor_dep', array('department_id' => $department_id));
		}

		$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		return $this->render('AdminBundle:TicketProperties:editor.html.twig', array(
			'departments' => $departments,
			'department' => $department,
			'custom_fields' => $custom_fields,
			'term_options' => $term_options
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
}