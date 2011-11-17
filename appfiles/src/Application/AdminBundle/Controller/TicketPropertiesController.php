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
		$department_names = App::getEntityRepository('DeskPRO:Department')->getDepartmentNames();

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

		$user_section = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSection($department, 'user', 'default');

		// Existing options
		//$current_state = array(
		//	'user_default'      => $user_section['data'],
		//	'agent_default'     => App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'agent', 'default'),
		//	'agent_toptabs'     => App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'agent', 'toptabs'),
		//	'agent_middletabs'  => App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'agent', 'middletabs'),
		//	'agent_bodytabs'    => App::getEntityRepository('DeskPRO:TicketPageDisplay')->getSectionData($department, 'agent', 'bodytabs'),
		//);

		return $this->render('AdminBundle:TicketProperties:editor.html.twig', array(
			'departments' => $departments,
			'department_names' => $department_names,
			'department' => $department,
			'custom_ticket_fields' => $custom_ticket_fields,
			'custom_people_fields' => $custom_people_fields,
			'term_options' => $term_options,
			'ticket_options' => $ticket_options,
			//'current_state' => $current_state,
			'user_section' => $user_section
		));
	}

	public function saveEditorAction($department_id)
	{
		$department = App::findEntity('DeskPRO:Department', $department_id);

		$page_displays = array();
		$editors = array('user_default', 'agent_default', 'agent_toptabs', 'agent_middletabs', 'agent_bodytabs');

		foreach ($editors as $editor) {
			$zone = strpos($editor, 'user_') === 0 ? 'user' : 'agent';
			$section = str_replace("{$zone}_", '', $editor);
			$page_display = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getOrCreate($department, $zone, $section);

			$page_data = $this->in->getArrayValue($editor, 'post');
			if (!$page_data) $page_data = array();

			$page_display['data'] = $page_data;

			$page_displays[$editor] = $page_display;
		}

		$enable_captcha = $this->in->getBool('enable_captcha');
		$user_page = $page_displays['user_default'];
		$user_page->setOption('enable_captcha', $enable_captcha);

		$user_dep_phrase_name = "obj_department.{$department_id}_title_user";
		$user_dep_phrase = App::getEntityRepository('DeskPRO:Phrase')->getPhraseForLanguage($user_dep_phrase_name);
		$user_dep_name = $this->in->getString('user_dep_name');

		if (!$user_dep_name OR $user_dep_name == $department['title']) {
			if ($user_dep_phrase) {
				App::getOrm()->remove($user_dep_phrase);
			}
		} else {
			if (!$user_dep_phrase) {
				$user_dep_phrase = new \Application\DeskPRO\Entity\Phrase();
				$user_dep_phrase['name'] = $user_dep_phrase_name;
			}

			$user_dep_phrase['phrase'] = $user_dep_name;

			App::getOrm()->persist($user_dep_phrase);
		}

		App::getOrm()->transactional(function($em) use ($page_displays) {
			foreach ($page_displays as $page_display) {
				$em->persist($page_display);
			}
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true));
	}
}
