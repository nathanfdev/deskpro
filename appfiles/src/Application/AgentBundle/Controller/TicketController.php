<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

/**
 * Handles ticket searches
 */
class TicketController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$person_inner_tab = $this->forward('AgentBundle:Person:view', array('person_id' => $ticket['person_id']))->getContent();
		//$person_inner_tab = '123';

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = new \Symfony\Component\Form\FieldGroup('custom_fields');

		$custom_fields = array();
		$has_value = false;
		foreach ($ticket_field_defs as $f_def) {
			$value = !empty($ticket_data_structured[$f_def['id']]) ? $ticket_data_structured[$f_def['id']] : null;
			
			$f = $f_def->getHandler()->getFormField($value);
			$custom_fields_form->add($f);

			$rendered = $value ? $f_def->getHandler()->renderHtml($value) : null;
			if ($rendered) $has_value = true;

			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'form' => $f,
				'rendered' =>  $rendered
			);
		}

		$ticket_flagged = APp::getOrm()->getRepository('DeskPRO:TicketFlagged')->find(array(
			'ticket_id' => $ticket_id,
			'person_id' => $this->person['id']
		));

		$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		return $this->render('AgentBundle:Ticket:view.twig.html', array(
			'person_inner_tab' => $person_inner_tab,
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
			'custom_fields_has_one_value' => $has_value,
			'ticket_flagged_color' => $ticket_flagged ? $ticket_flagged['color'] : 'none',
			'macros' => $macros
		));
	}

	

	############################################################################
	# new
	############################################################################

	public function newAction()
	{
		$ticket = new Entity\Ticket();

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$custom_fields_form = new \Symfony\Component\Form\FieldGroup('custom_fields');

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields = array();
		foreach ($ticket_field_defs as $f_def) {
			$value = !empty($ticket_data_structured[$f_def['id']]) ? $ticket_data_structured[$f_def['id']] : null;

			$f = $f_def->getHandler()->getFormField($value);
			$custom_fields_form->add($f);

			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'form' => $f,
				'rendered' => false
			);
		}

		$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		return $this->render('AgentBundle:Ticket:new-ticket.twig.html', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
			'macros' => $macros
		));
	}

	// Handles actually creating the ticket now
	public function newAjaxSaveAction()
	{
		App::getOrm()->beginTransaction();

		$ticket = new Entity\Ticket();
		$ticket['department_id']  = $this->in->getUint('ticket.department_id');
		$ticket['category_id']    = $this->in->getUint('ticket.category_id');
		$ticket['product_id']     = $this->in->getUint('ticket.product_id');
		$ticket['priority_id']    = $this->in->getUint('ticket.department_id');
		$ticket['status']         = $this->in->getString('ticket.status');
		$ticket['person_id']      = $this->in->getUint('ticket.person_id');

		$ticket['subject']      = $this->in->getString('ticket.subject');

		if (!$ticket['status']) {
			$ticket['status'] = Entity\Ticket::STATUS_OPEN;
		}

		$ticket['creation_system'] = Entity\Ticket::CREATED_WEB_AGENT;

		$message = new Entity\TicketMessage();
		$message['person'] = $this->person;
		$message['message'] = $this->in->getString('message');
		$ticket->addMessage($message);

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_field_datas = array();
		foreach ($ticket_field_defs as $field_def) {
			$ticket_field_datas = Arrays::mergeAssoc($ticket_field_datas, $field_def->getHandler()->getDataFromForm($_POST['custom_fields']));
		}

		foreach ($ticket_field_datas as $field_id => $data) {
			$ticket->setCustomData($field_id, $data);
		}

		App::getOrm()->persist($ticket);
		App::getOrm()->flush();
		App::getOrm()->commit();

		$data = array(
			'id' => $ticket['id'],
			'loadUrl' => $this->generateUrl('agent_ticket_view', array('ticket_id' => $ticket['id']))
		);

		return $this->createJsonResponse($data);
	}


	############################################################################
	# ajax-save-flagged
	############################################################################

	public function ajaxSaveFlaggedAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		
		$ticket_flagged = APp::getOrm()->getRepository('DeskPRO:TicketFlagged')->find(array(
			'ticket_id' => $ticket_id,
			'person_id' => $this->person['id']
		));
		if (!$ticket_flagged) {
			$ticket_flagged = new Entity\TicketFlagged();
			$ticket_flagged['ticket_id'] = $ticket_id;
			$ticket_flagged['person_id'] = $this->person['id'];
		}

		$ticket_flagged['color'] = $this->in->getString('color');

		if ($ticket_flagged['color'] == 'none') {
			if (App::getOrm()->contains($ticket_flagged)) {
				// If its an existing record, we wanna delete it
				App::getOrm()->remove($ticket_flagged);
			}
		} else {
			App::getOrm()->persist($ticket_flagged);
		}

		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}



	############################################################################
	# ajax-save-custom-fields
	############################################################################

	public function ajaxSaveCustomFieldsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_field_datas = array();
		foreach ($ticket_field_defs as $field_def) {
			$ticket_field_datas = Arrays::mergeAssoc($ticket_field_datas, $field_def->getHandler()->getDataFromForm($_POST['custom_fields']));
		}

		$ticket_edit->setCustomDataAll($ticket_field_datas);
		$ticket_edit->save();

		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);
		$custom_fields = array();
		foreach ($ticket_field_defs as $f_def) {
			$f = $f_def->getHandler()->getFormField();

			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'rendered' => $ticket_data_structured[$f_def['id']] ? $f_def->getHandler()->renderHtml($ticket_data_structured[$f_def['id']]) : false
			);
		}

		return $this->render('AgentBundle:Ticket:custom-fields-rendered.twig.html', array(
			'custom_fields' => $custom_fields,
		));
	}



	############################################################################
	# ajax-save-options
	############################################################################

	public function ajaxSaveOptionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		if ($this->in->checkIsset('department')) {
			$ticket['department_id'] = $this->in->getUint('department');
		}
		if ($this->in->checkIsset('category')) {
			$ticket['category_id'] = $this->in->getUint('category');
		}
		if ($this->in->checkIsset('product')) {
			$ticket['product_id'] = $this->in->getUint('product');
		}
		if ($this->in->checkIsset('priority')) {
			$ticket['priority_id'] = $this->in->getUint('priority');
		}
		if ($this->in->checkIsset('status')) {
			$ticket['status'] = $this->in->getString('status');
		}
		if ($this->in->checkIsset('agent')) {
			$ticket['agent_id'] = $this->in->getUint('agent');
		}
		if ($this->in->checkIsset('agent_team')) {
			$ticket['agent_team_id'] = $this->in->getUint('agent_team');
		}

		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		$ticket_edit->save();

		return $this->createJsonResponse(array('success' => 1));
	}

	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$ticket->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# ajax-save-reply
	############################################################################

	public function ajaxSaveReplyAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['message'] = $this->in->getString('message');

		foreach ($this->in->getArrayValue('attach') as $info) {

			if (!$info['save']) {
				continue;
			}

			$path = Util::coalesce(ini_get("upload_tmp_dir"), sys_get_temp_dir()) . DIRECTORY_SEPARATOR . "dpupload/" . $info['tmp_name'];
			$file = new \Symfony\Component\HttpFoundation\File\File($path);

			$desc = App::getApi('filestorage')->createRandomPath();

			$fp = fopen($path, 'r');
			$desc->writeFromFile($fp, array(
				'content_type' => $file->getMimeType(),
				'filename' => $info['name']
			));
			fclose($fp);

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;
			
			$message->addAttachment($attach);
		}

		$ticket_edit->addMessage($message);
		$ticket_edit->save();

		return $this->render('AgentBundle:Ticket:ticket-message.twig.html', array(
			'message' => $message
		));
	}


	############################################################################
	# ajax-ticket-log
	############################################################################

	public function ajaxTicketLogAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_logs = App::getOrm()->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket);

		return $this->render('AgentBundle:Ticket:ticketlog.twig.html', array(
			'ticket_logs' => $ticket_logs
		));
	}


	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id)
	{
		$ticket = $this->em->find('DeskPRO:Ticket', $ticket_id);

		if (!$ticket) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}