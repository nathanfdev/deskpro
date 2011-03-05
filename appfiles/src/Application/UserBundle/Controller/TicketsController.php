<?php

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

class TicketsController extends AbstractController
{
	################################################################################
	# new-ticket
	################################################################################

	/**
	 * Create a new ticket
	 */
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

		if ($this->in->getBool('process')) {
			App::getOrm()->beginTransaction();

			$ticket = new Entity\Ticket();
			$ticket['department_id']  = $this->in->getUint('department_id');
			$ticket['category_id']    = $this->in->getUint('category_id');
			$ticket['product_id']     = $this->in->getUint('product_id');
			$ticket['priority_id']    = $this->in->getUint('priority_id');
			$ticket['workflow_id']    = $this->in->getUint('workflow_id');
			$ticket['status']         = 'open';
			$ticket['person_id']      = $this->person['id'];

			$ticket['subject']      = $this->in->getString('subject');
			$ticket['creation_system'] = Entity\Ticket::CREATED_WEB_PERSON;

			$message = new Entity\TicketMessage();
			$message['person'] = $this->person;
			$message['message'] = $this->in->getString('message');
			$ticket->addMessage($message);

			// Custom fields
			/*
			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			$ticket_field_datas = array();
			foreach ($ticket_field_defs as $field_def) {
				$ticket_field_datas = Arrays::mergeAssoc($ticket_field_datas, $field_def->getHandler()->getDataFromForm($_POST['custom_fields']));
			}

			foreach ($ticket_field_datas as $info) {
				$ticket->setCustomData($info[0], $info[1], $info[2]);
			}

			 */

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
			App::getOrm()->commit();

			return $this->redirectRoute('user_tickets_view', array('ticket_id' => $ticket['id']));
		}

		return $this->render('UserBundle:Tickets:new-ticket.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields
		));
    }



	################################################################################
	# list
	################################################################################

	/**
	 * View a list of all tickets
	 */
    public function listAction()
    {
		$tickets = App::getOrm()->createQuery("
			SELECT ticket
			FROM DeskPRO:Ticket ticket
			WHERE ticket.person = :person
			ORDER BY ticket.id DESC
		")->execute(array('person' => $this->person));

        return $this->render('UserBundle:Tickets:list.html.twig', array(
			'tickets' => $tickets
		));
    }



	################################################################################
	# view
	################################################################################

	/**
	 * View a ticket
	 */
	public function viewAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured);

		// Widgets
		$widget_recs = App::getEntityRepository('DeskPRO:Widget')->getWidgetsForSection('user.ticket');
		$widgets = array();
		if (count($widget_recs)) {
			$widgets = \Application\DeskPRO\Widgets\Factory::createHandlersForWidgets(
				$widget_recs,
				array('ticket' => $ticket, 'person' => $this->person)
			);
		}

		$widgets = Arrays::groupItems($widgets, 'section', true);
		if (!isset($widgets['user.ticket.display'])) $widgets['user.ticket.display'] = array();

		return $this->render('UserBundle:Tickets:view.html.twig', array(
			'ticket' => $ticket,
			'custom_fields' => $custom_fields,
			'widgets' => $widgets
		));
	}

	/**
	 * @return Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_id)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($ticket_id);

		if (!$ticket OR $ticket['person_id'] != $this->person['id']) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_id");
		}

		return $ticket;
	}
}
