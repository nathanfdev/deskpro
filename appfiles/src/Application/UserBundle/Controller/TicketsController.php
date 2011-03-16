<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Application\UserBundle\Form\NewTicketForm;
use \Application\UserBundle\Form\NewTicketUserForm;

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

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, array(), $custom_fields_form);

		$new_ticket = new \Application\UserBundle\NewTicket();
		if ($this->person['id']) {
			$new_ticket->setPerson($this->person);
		}
		$new_ticket_form = new NewTicketForm('newticket', array(
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields_form
		));

		$new_ticket_form->bind($this->request, $new_ticket);

		if ($this->in->getBool('process')) {

			$new_ticket->save();

			// If this is a new person, we will ask them to complete registrion by
			// choosing a password etc
			if ($new_ticket->is_new_person) {
				$this->session->set('finish_register_person', $new_ticket->person['id']);
				$this->session->set('after_register', $this->generateUrl('user_tickets_view', array('ticket_id' => $ticket['id']), true));
				return $this->redirectRoute('user_register_finish');
			}

			// If this isnt a new person but they arent registered, we have no choice but
			// to show a standard confirmation page. They'll get a link in their email to view
			// the web interface. But we cant give them another chance to register now incase
			// this user is an imposter. The confirmation email we send serves doubly as a
			// confirmation in that case
			if (!$new_ticket->person['is_user']) {
				return $this->redirectRoute('user_tickets_new_thanks', array('ticket_id' => $ticket['id']));
			}

			// We get here if the user is a real user, they should already be logged in then
			return $this->redirectRoute('user_tickets_view', array('ticket_id' => $ticket['id']));
		}

		return $this->render('UserBundle:Tickets:new-ticket.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
			'form' => $new_ticket_form,
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
