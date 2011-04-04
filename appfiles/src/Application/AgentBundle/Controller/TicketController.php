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
		$ticket = $this->getTicketOr404($ticket_id, true);
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		$ticket_flagged = APp::getOrm()->getRepository('DeskPRO:TicketFlagged')->find(array(
			'ticket_id' => $ticket_id,
			'person_id' => $this->person['id']
		));

		$macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

		$tpl = 'AgentBundle:Ticket:view.html.twig';
		if ($this->in->getBool('print')) {
			$tpl = 'AgentBundle:Ticket:view-print.html.twig';
		}

		// Get or update the lock on this ticket
		if (!$ticket->isLocked()) {
			$ticket->setLockedByAgent($this->person);
			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
		}

		// Widgets
		$widget_recs = App::getEntityRepository('DeskPRO:Widget')->getWidgetsForSection('agent.ticket');
		$widgets = array();
		if (count($widget_recs)) {
			$widgets = \Application\DeskPRO\Widgets\Factory::createHandlersForWidgets(
				$widget_recs,
				array('ticket' => $ticket, 'person' => $this->person)
			);
		}

		$widgets = Arrays::groupItems($widgets, 'section', true);
		if (!isset($widgets['agent.ticket.tabs'])) $widgets['agent.ticket.tabs'] = array();
		if (!isset($widgets['agent.ticket.display'])) $widgets['agent.ticket.display'] = array();

		$ticket_deleted = null;
		if ($ticket['hidden_status'] == 'deleted') {
			$ticket_deleted = $ticket->getDeletionRecord();
		}

		return $this->render($tpl, array(
			'ticket' => $ticket,
			'ticket_deleted' => $ticket_deleted,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
			'ticket_flagged_color' => $ticket_flagged ? $ticket_flagged['color'] : 'none',
			'macros' => $macros,
			'widgets' => $widgets,
			'counts' => $this->_fetchTicketCounts($ticket)
		));
	}

	protected function _fetchTicketCounts($ticket)
	{
		$counts = array();
		$counts['messages'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_messages
			WHERE ticket_id = ?
		", array($ticket['id']));

		$counts['notes'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_messages
			WHERE ticket_id = ? AND is_agent_note = 1
		", array($ticket['id']));

		$counts['attachments'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_attachments
			WHERE ticket_id = ?
		", array($ticket['id']));

		$counts['logs'] = App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM tickets_logs
			WHERE ticket_id = ?
		", array($ticket['id']));

		return $counts;
	}

	public function getUpdatedCountsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		return $this->createJsonResponse($this->_fetchTicketCounts($ticket));
	}

	############################################################################
	# view-tip
	############################################################################

	/**
	 * Serves up a tool-tip description for the ticket
	 *
	 * @param  $ticket_id
	 */
	public function viewTipAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$message = null;
		try {
			$message = App::getEntityRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($ticket);
		} catch (\Exception $e) {};

		return $this->render('AgentBundle:Ticket:ticket-tip.html.twig', array(
			'ticket' => $ticket,
			'message' => $message
		));
	}



	############################################################################
	# new
	############################################################################

	public function newAction()
	{
		$ticket = new Entity\Ticket();

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields');

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

		$initial_person = null;
		if ($this->in->getUint('person_id')) {
			$initial_person = App::getEntityRepository('DeskPRO:Person')->find($this->in->getUint('person_id'));
		}

		if ($initial_person) {
			$ticket['person'] = $initial_person;
		}

		return $this->render('AgentBundle:Ticket:new-ticket.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
			'macros' => $macros,
			'initial_person' => $initial_person
		));
	}

	// Handles actually creating the ticket now
	public function newAjaxSaveAction()
	{
		App::getOrm()->beginTransaction();

		$ticket = new Entity\Ticket();
		$ticket['agent_id']  = $this->in->getUint('agent_id');
		$ticket['agent_team_id']  = $this->in->getUint('agent_team_id');
		$ticket['department_id']  = $this->in->getUint('department_id');
		$ticket['category_id']    = $this->in->getUint('category_id');
		$ticket['product_id']     = $this->in->getUint('product_id');
		$ticket['priority_id']    = $this->in->getUint('priority_id');
		$ticket['workflow_id']    = $this->in->getUint('workflow_id');
		$ticket['status']         = $this->in->getString('status');
		$ticket['person_id']      = max($this->in->getUint('person_id'), $this->person['id']); // TODO handle no person selected

		$ticket['subject']      = $this->in->getString('subject');

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
			$ticket_field_datas = Arrays::mergeAssoc($ticket_field_datas, $field_def->getHandler()->getDataFromForm(isset($_POST['custom_fields']) ? $_POST['custom_fields'] : array()));
		}

		foreach ($ticket_field_datas as $info) {
			$ticket->setCustomData($info[0], $info[1], $info[2]);
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
	# Ajax loaded tabs
	############################################################################

	public function ajaxTabTicketLogAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_logs = App::getOrm()->getRepository('DeskPRO:TicketLog')->getLogsForTicket($ticket);

		return $this->render('AgentBundle:Ticket:tab-ticketlog.html.twig', array(
			'ticket_logs' => $ticket_logs
		));
	}

	public function ajaxTabAttachmentsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		return $this->render('AgentBundle:Ticket:tab-attachments.html.twig', array(
			'ticket' => $ticket
		));
	}

	############################################################################
	# ajax-save-flagged
	############################################################################

	public function ajaxSaveFlaggedAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket->setFlagForPerson($this->person, $this->in->getString('color'));

		return $this->createJsonResponse(array('success' => 1));
	}



	############################################################################
	# ajax-save-custom-fields
	############################################################################

	public function ajaxSaveCustomFieldsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		foreach ($ticket_field_defs as $field_def) {
			foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
				$ticket->setCustomData($info[0], $info[1], $info[2]);
			}
		}

		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

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

		return $this->render('AgentBundle:Ticket:custom-fields-rendered.html.twig', array(
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

		foreach ($this->in->getCleanValueArray('attach') as $blob_id) {

			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$message->addAttachment($attach);
		}

		$ticket_edit->addMessage($message);
		$ticket_edit->save();

		return $this->render('AgentBundle:Ticket:ticket-message.html.twig', array(
			'message' => $message
		));
	}

	public function ajaxSaveNoteAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $this->person;
		$message['message'] = $this->in->getString('message');
		$message['is_agent_note'] = true;

		$ticket_edit->addMessage($message);
		$ticket_edit->save();

		return $this->render('AgentBundle:Ticket:ticket-message.html.twig', array(
			'message' => $message
		));
	}

	############################################################################
	# ajax-save-actions
	############################################################################

	public function ajaxSaveActionsAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		$result = $ticket_edit->applyActions($this->in->getCleanValueArray('actions', 'raw', 'raw'));

		App::getOrm()->beginTransaction();

		$macro_id = $this->in->getUint('macro_id');
		if ($macro_id) {
			$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);
			$all_macro_actions = $macro->getActionsArray($ticket);
			$apply_macro_actions = array();

			// Only ticket fields need to be applied this way,
			// the other actions were performed on the actual ticket interface
			// and sent in the request, and applied normally above
			foreach ($all_macro_actions as $k => $action) {
				if (strpos($k, 'ticket_field') === 0) {
					$apply_macro_actions[$k] = $action;
				}
			}

			// We need to manually apply to the user since ticketedit doesnt care about that
			$macro->performOnPerson($ticket['person']);

			if ($apply_macro_actions) {
				$ticket_edit->applyActions($apply_macro_actions);
			}
		}

		$ticket_edit->save();

		App::getOrm()->flush();
		App::getOrm()->commit();

		$data = array();
		if (isset($result['new_reply'])) {
			$data['new_reply'] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $result['new_reply']
			));
		}

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-get-macro-actions
	############################################################################

	public function ajaxGetMacroAction($ticket_id)
	{
		$macro_id = $this->in->getUint('macro_id');
		$macro = App::getEntityRepository('DeskPRO:TicketMacro')->find($macro_id);

		$ticket = null;
		if ($ticket_id) {
			$ticket = $this->getTicketOr404($ticket_id);
		}

		$actions = $macro->getActionsArray($ticket);

		return $this->createJsonResponse($actions);
	}


	############################################################################
	# view-message-details
	############################################################################

	public function viewUnformattedMessageAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);

		return $this->render('AgentBundle:Ticket:message-details-unformatted.html.twig', array(
			'message' => $message,
			'ticket' => $message['ticket']
		));
	}

	public function ajaxGetMessageQuoteAction($message_id)
	{
		$message = App::getEntityRepository('DeskPRO:TicketMessage')->find($message_id);

		$message_quote = wordwrap($message->getMessageText(), 75, "\n", true);
		$message_quote = preg_replace('#^#m', "> ", $message_quote);

		return $this->createJsonResponse(array(
			'message_id' => $message['id'],
			'message_quote' => $message_quote
		));
	}

	############################################################################
	# get-ticket-messages
	############################################################################

	public function ajaxGetMessagesAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$data = array('messages' => array());

		$since = $this->in->getUint('since');

		$messages = App::getOrm()->createQuery("
			SELECT m
			FROM DeskPRO:TicketMessage m
			WHERE m.ticket = ?1 AND m.id > ?2
		")->execute(array(1=>$ticket, 2=> $since));

		foreach ($messages as $message) {
			$data['messages'][] = $this->renderView('AgentBundle:Ticket:ticket-message.html.twig', array(
				'message' => $message
			));

			if ($message['is_agent_note']) {
				$data['has_notes'] = true;
			}
		}

		return $this->createJsonResponse($data);
	}

	############################################################################
	# delete
	############################################################################

	/**
	 * Soft-deletes a ticket
	 *
	 * @param  $ticket_id
	 */
	public function deleteAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		$reason = $this->in->getString('reason');

		App::getOrm()->beginTransaction();
		$ticket->deleteTicket($this->person, $reason);
		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}


	############################################################################

	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id, $full = false)
	{
		if ($full) {
			$ticket = App::getOrm()->createQuery("
				SELECT
					ticket, messages, message_person,
					ticket_parts, ticket_dep, ticket_prod, ticket_cat, ticket_pri,
					ticket_person, ticket_person_email, ticket_agent, ticket_agent_team,
					ticket_attach, ticket_custom_data, ticket_custom_data_field
				FROM DeskPRO:Ticket ticket
				LEFT JOIN ticket.messages messages
				LEFT JOIN messages.person message_person
				LEFT JOIN ticket.participants ticket_parts
				LEFT JOIN ticket.department ticket_dep
				LEFT JOIN ticket.product ticket_prod
				LEFT JOIN ticket.category ticket_cat
				LEFT JOIN ticket.priority ticket_pri
				LEFT JOIN ticket.person ticket_person
				LEFT JOIN ticket.person_email ticket_person_email
				LEFT JOIN ticket.agent ticket_agent
				LEFT JOIN ticket.agent_team ticket_agent_team
				LEFT JOIN ticket.attachments ticket_attach
				LEFT JOIN ticket.custom_data ticket_custom_data
				LEFT JOIN ticket_custom_data.field ticket_custom_data_field
				WHERE ticket.id = ?1
			")->setParameter(1, $ticket_id)->getSingleResult();
		} else {
			$ticket = $this->em->find('DeskPRO:Ticket', $ticket_id);
		}

		if (!$ticket) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}