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

use Application\AdminBundle\Form\EditTicketPriorityType;

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
	public function editorAction($department_id, $section = 'create')
	{
		$departments = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();
		$department_hierarchy = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();

		$department = null;
		if ($department_id) {
			$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		}

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_ticket_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);

		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$custom_people_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		// Existing options
		$is_default = false;
		$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, $section, 'default');
		if (!$page_data && $department_id) {
			$is_default = true;
			$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData(null, $section, 'default');
		}


		return $this->render('AdminBundle:TicketProperties:editor.html.twig', array(
			'departments' => $departments,
			'department_hierarchy' => $department_hierarchy,
			'department' => $department,
			'custom_ticket_fields' => $custom_ticket_fields,
			'custom_people_fields' => $custom_people_fields,
			'term_options' => $term_options,
			'ticket_options' => $ticket_options,
			'is_default' => $is_default,
			'page_data' => $page_data,
			'section' => $section
		));
	}

	public function saveEditorAction($department_id, $section = 'create')
	{
		$department = null;

		if ($department_id) {
			$department = App::findEntity('DeskPRO:Department', $department_id);
		}

		$page_data = $this->in->getArrayValue('items', 'post');
		$page_display = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getOrCreate($department, $section, 'default');
		$page_display->data = $page_data;

		App::getOrm()->transactional(function($em) use ($page_display) {
			$em->persist($page_display);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true));
	}

	public function copyDefaultEditorAction($department_id)
	{
		if (!$department_id) {
			return $this->redirectRoute('admin_tickets_editor');
		}

		foreach (array('create', 'view', 'modify') as $section) {
			$page_display_default = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getOrCreate(null, $section, 'default');

			$department = $this->em->find('DeskPRO:Department', $department_id);
			$page_display = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getOrCreate($department, $section, 'default');
			$page_display->data = $page_display_default->data;
			App::getOrm()->transactional(function($em) use ($page_display) {
				$em->persist($page_display);
				$em->flush();
			});
		}

		return $this->redirectRoute('admin_tickets_editor_dep', array('department_id' => $department_id));
	}

	public function revertEditorAction($department_id)
	{
		if (!$department_id) {
			return $this->redirectRoute('admin_tickets_editor');
		}

		foreach (array('create', 'view', 'modify') as $section) {
			$d = $this->em->getRepository('DeskPRO:TicketPageDisplay')->findOneBy(array('department' => $department_id, 'zone' => $section, 'section' => 'default'));
			if ($d) {
				App::getOrm()->transactional(function($em) use ($d) {
					$em->remove($d);
					$em->flush();
				});
			}
		}

		return $this->redirectRoute('admin_tickets_editor_dep', array('department_id' => $department_id));
	}


	############################################################################
	# form-embed
	############################################################################

	public function formEmbedAction($department_id)
	{
		$department_hierarchy = App::getEntityRepository('DeskPRO:Department')->getDepartmentsInHierarchy();

		$department = null;
		if ($department_id) {
			$department = App::findEntity('DeskPRO:Department', $department_id);
		}

		return $this->render('AdminBundle:TicketProperties:form-embed.html.twig', array(
			'department' => $department,
			'department_hierarchy' => $department_hierarchy,
		));
	}
}
