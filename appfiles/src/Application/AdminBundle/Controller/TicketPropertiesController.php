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
		$counts['department']      = App::getEntityRepository('DeskPRO:Department')->countAll();
		$counts['product']         = App::getEntityRepository('DeskPRO:Product')->countAll();

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

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		return $this->render('AdminBundle:TicketProperties:editor.html.twig', array(
			'departments' => $departments,
			'department' => $department,
			'custom_fields' => $custom_fields,
			'term_options' => $term_options,
			'ticket_options' => $ticket_options
		));
	}

	public function saveEditorAction($department_id)
	{
		$department = App::findEntity('DeskPRO:Department', $department_id);
		$zone = $this->in->getString('zone');
		if (!$zone) $zone = 'user';

		$ticket_page_display = App::getEntityRepository('DeskPRO:TicketPageDisplay')->findOneBy(array('department_id' => $department_id, 'zone' => $zone));
		if (!$ticket_page_display) {
			$ticket_page_display = new Entity\TicketPageDisplay();
			$ticket_page_display['zone'] = $zone;
			$ticket_page_display->department = $department;
		}

		$ticket_page_display['data'] = $this->in->getArrayValue('items');

		App::getOrm()->transactional(function($em) use ($ticket_page_display) {
			$em->persist($ticket_page_display);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true));
	}
}