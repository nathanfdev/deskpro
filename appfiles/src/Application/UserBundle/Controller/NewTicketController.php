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
		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket($this->person);

		$form = new NewTicketForm('newticket', array(
			'person' => $this->person,
		));

		$form->bind($this->get('request'), $newticket);

		if ($form->isValid()) {
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

		return $this->render('UserBundle:NewTicket:new-ticket.html.twig', array(
			'ticket_options' => $form->getTicketOptions(),
			'custom_fields' => $form->getTicketFields(),
			'form' => $form,
		));
    }
}
