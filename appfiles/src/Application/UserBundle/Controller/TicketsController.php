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

use \Application\UserBundle\Form\EditTicketType;
use \Application\UserBundle\Form\NewTicketReplyType;

class TicketsController extends AbstractController
{
	protected $session_allowed = array();

	protected function init()
	{
		parent::init();

		if ($this->session->get('ticket_access')) {
			$this->session_allowed = $this->session->get('ticket_access');
		}
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
	public function viewAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

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

		$newply_form = $this->get('form.factory')->create(new NewTicketReplyType());

		return $this->render('UserBundle:Tickets:view.html.twig', array(
			'ticket' => $ticket,
			'custom_fields' => $custom_fields,
			'widgets' => $widgets,
			'newreply_form' => $newply_form->createView()
		));
	}

	################################################################################
	# view-with-auth
	################################################################################

	/**
	 * View a ticket
	 */
	public function viewWithAuthAction($ticket_ref, $ticket_auth)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);
		if (!$ticket OR !($tac = $ticket->findAccessCode($ticket_auth))) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		// Add this ticket to allowed tickets in session
		$this->session_allowed[$ticket['id']] = array('person_id' => $tac->person['id']);
		$this->session->set('ticket_access', $this->session_allowed);

		// And also mark the user and ticket as validated
		App::getOrm()->beginTransaction();

		$ticket->person_email['is_validated'] = true;
		$ticket['status'] = Entity\Ticket::STATUS_OPEN;

		App::getOrm()->persist($ticket->person_email);
		App::getOrm()->persist($ticket);
		App::getOrm()->flush();
		App::getOrm()->commit();

		// Regular ticket page
		return $this->viewAction($ticket_ref);
	}

	################################################################################
	# add-reply
	################################################################################

	/**
	 * Only posted forms get here. The actual form is on the viewtikcet page.
	 * 
	 * @param  $ticket_ref
	 */
	public function addReplyAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		$newreply = new \Application\UserBundle\Tickets\NewReply($ticket, $this->person);

		$form = $this->get('form.factory')->create(new NewTicketReplyType(), $newreply);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$newreply->save();
				$ticket_message = $newreply->getNewMessage();
			}
		}

		return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket_ref));
	}

	################################################################################
	# modify
	################################################################################

	/**
	 * Modify ticket
	 */
	public function modifyAction($ticket_ref)
	{
		$ticket = $this->getTicketOr404($ticket_ref);

		$editticket = new \Application\UserBundle\Tickets\EditTicket\EditTicket(
			$ticket
		);
		
		$editticket_formtype = new EditTicketType($this->person);
		$form = $this->get('form.factory')->create($editticket_formtype, $editticket);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->transactional(function() use ($ticket) {
					App::getOrm()->persist($ticket);
					App::getOrm()->flush();
				});

				return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket_ref));
			}
		}

		return $this->render('UserBundle:Tickets:modify-ticket.html.twig', array(
			'ticket_options' => $editticket_formtype->getTicketOptions(),
			'editticket_formtype' => $editticket_formtype,
			'custom_fields' => $editticket_formtype->getTicketFields(),
			'form' => $form->createView(),
			'ticket' => $ticket
		));
	}



	/**
	 * @return Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_ref, $authcode = null)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);

		if (!$ticket OR ($ticket['person_id'] != $this->person['id'] AND !isset($this->session_allowed[$ticket['id']]))) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_ref");
		}

		if (isset($this->session_allowed[$ticket['id']])) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($this->session_allowed[$ticket['id']]['person_id']);

			// Set the current person context
			if ($this->person != $person) {
				$this->person = $person;
				App::setCurrentPerson($person);
			}
		}

		return $ticket;
	}
}
