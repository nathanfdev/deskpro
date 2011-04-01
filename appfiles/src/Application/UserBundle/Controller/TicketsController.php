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

		return $this->render('UserBundle:Tickets:view.html.twig', array(
			'ticket' => $ticket,
			'custom_fields' => $custom_fields,
			'widgets' => $widgets
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
		// If we're currently logged in, then the auth is meaningless
		if ($this->person['id']) {
			return $this->view($ticket_ref);
		}
		
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);
		if (!$ticket OR $ticket['code'] != $ticket_auth) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		// Add this ticket to allowed tickets in session
		$this->session_allowed[] = $ticket['id'];
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

	/**
	 * @return Application\DeskPRO\Entity\Ticket
	 */
	protected function getTicketOr404($ticket_ref, $authcode = null)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);

		if (!$ticket OR ($ticket['person_id'] != $this->person['id'] AND !in_array($ticket['id'], $this->session_allowed))) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no ticket with ID $ticket_ref");
		}

		// Set the current person context
		if ($this->person != $ticket->person) {
			$this->person = $ticket->person;
			App::setCurrentPerson($ticket->person);
		}

		return $ticket;
	}
}
