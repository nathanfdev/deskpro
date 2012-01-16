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
		// If no per-department forms, then we cant edit a department
		if ($department_id AND !App::getSetting('core_tickets.per_department_form')) {
			return $this->redirectRoute('admin_tickets_editor');
		}

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

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$ticket_options['email_gateway_addresses'] = $this->em->getRepository('DeskPRO:EmailGatewayAddress')->getOptions();

		// Existing options
		$is_default = false;
		$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, $section, 'default');
		if ($page_data === null && $department_id) {
			$is_default = true;

			if ($section == 'create') {
				$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData(null, $section, 'default');
			} else {
				// Default for view/modify is the create form from the same department,
				// or the create form from the default

				$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'create', 'default');
				if (!$page_data) {
					$page_data = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData(null, 'create', 'default');
				}
			}
		}

		return $this->render('AdminBundle:TicketProperties:editor.html.twig', array(
			'departments' => $departments,
			'department_hierarchy' => $department_hierarchy,
			'department' => $department,
			'custom_ticket_fields' => $custom_ticket_fields,
			'custom_people_fields' => $custom_people_fields,
			'term_options' => $ticket_options,
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

	public function initEditorAction($department_id, $section)
	{
		$department = App::findEntity('DeskPRO:Department', $department_id);

		if (!$department) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($section == 'create') {
			$page_display_default = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData(null, $section, 'default');
		} else {
			$page_display_default = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'create', 'default');
			if (!$page_display_default === null) {
				$page_display_default = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData(null, 'create', 'default');
			}
		}

		$page_display = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getOrCreate($department, $section, 'default');
		$page_display->data = $page_display_default;

		App::getOrm()->transactional(function($em) use ($page_display) {
			$em->persist($page_display);
			$em->flush();
		});

		if ($department) {
			return $this->redirectRoute('admin_tickets_editor_dep', array('department_id' => $department->id, 'section' => $section));
		} else {
			return $this->redirectRoute('admin_tickets_editor', array('section' => $section));
		}
	}

	public function revertEditorAction($department_id, $section)
	{
		if (!$department_id) {
			return $this->redirectRoute('admin_tickets_editor');
		}

		$d = $this->em->getRepository('DeskPRO:TicketPageDisplay')->findOneBy(array('department' => $department_id, 'zone' => $section, 'section' => 'default'));
		if ($d) {
			App::getOrm()->transactional(function($em) use ($d) {
				$em->remove($d);
				$em->flush();
			});
		}

		if ($department_id) {
			return $this->redirectRoute('admin_tickets_editor_dep', array('department_id' => $department_id, 'section' => $section));
		} else {
			return $this->redirectRoute('admin_tickets_editor', array('section' => $section));
		}
	}

	public function togglePerDepartmentAction()
	{
		$enable = $this->in->getBoolInt('enable');
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core_tickets.per_department_form', $enable);

		return $this->redirectRoute('admin_tickets_editor');
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
