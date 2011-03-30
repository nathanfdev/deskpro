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

class NewTicketController extends AbstractController
{
	################################################################################
	# new-ticket
	################################################################################

	/**
	 * Create a new ticket
	 */
    public function newAction()
    {
		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);

		$form = new NewTicketForm('newticket', array(
			'person' => $this->person,
			'validator' => $this->get('validator')
		));

		$form->bind($this->get('request'), $newticket);

		if ($form->isValid()) {
			$ticket = $newticket->save();
			$person = $ticket['person'];

			// If this is a new person, we'll forward them to full reg page
			if ($person->isNewPerson()) {
				$this->session->set('finish_register_person', $person['id']);
				$this->session->set('after_register', $this->generateUrl('user_tickets_view', array('ticket_ref' => $ticket['ref']), true));

				$ticket_access = $this->session->get('ticket_access', array());
				$ticket_access[] = $ticket['id'];
				$this->session->set('ticket_access', $ticket_access);

				return $this->redirectRoute('user_register_finish');
			}

			// If this isnt a new person but they arent registered, we have no choice but
			// to show a standard thanks page.
			// - We cant direct them right to the ticket because the user might be an imposter
			// of an previously submitted email, and showing them the full ticket might reveal
			// other personal info such as custom fields/widgets
			// - And we cant redirect them to full registration for the same reason
			// - They'll get an email with a link to the web interface though, so this isnt so bad
			if (!$person['is_user']) {
				return $this->redirectRoute('user_tickets_new_thanks', array('ticket_ref' => $ticket['ref']));
			}

			// We get here if the user is a real user, they should already be logged in then
			return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket['ref']));
		}

		return $this->render('UserBundle:NewTicket:new-ticket.html.twig', array(
			'ticket_options' => $form->getTicketOptions(),
			'custom_fields' => $form->getTicketFields(),
			'form' => $form,
		));
    }
}
