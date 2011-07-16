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

use \Application\UserBundle\Form\NewTicketType;

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

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {

				$ticket = $newticket->save();
				$person = $ticket['person'];

				// If this is a new person, we'll forward them to full reg page
				if ($person->isNewPerson()) {
					$this->session->set('finish_register_person', $person['id']);
					$this->session->set('finish_register_mode', array('type' => 'ticket', 'id' => $ticket['id']));
					$this->session->set('after_register', $this->generateUrl('user_tickets_view', array('ticket_ref' => $ticket['ref']), true));

					$ticket_access = $this->session->get('ticket_access', array());
					$ticket_access[$ticket['id']] = array('person_id' => $ticket['person_id']);
					$this->session->set('ticket_access', $ticket_access);

					return $this->redirectRoute('user_register_finish');
				}

				// If the person is a user, but they arent logged in, then they have to now
				if ($person['is_user'] AND $this->person['id'] != $person['id']) {
					return $this->redirectRoute('user_login', array('return' => $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket['ref']))));
				}

				// If this isnt a new person but they arent registered, we have no choice but
				// to show a standard thanks page.
				// - We cant direct them right to the ticket because the user might be an imposter
				// of an previously submitted email, and showing them the full ticket might reveal
				// other personal info in custom fields/widgets
				// - And we cant redirect them to full registration for the same reason
				// - They'll get an email with a link to the web interface though, so at that point we know they're true
				if (!$person['is_user']) {
					$this->session->set('submitted_ticket', $ticket['ref']);
					return $this->redirectRoute('user_tickets_new_thanks', array('ticket_ref' => $ticket['ref']));
				}

				// We get here if the user is a real user and they're logged in
				return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket['ref']));
			}
		}

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array(), $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		$ticket_display = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('user');
		$ticket_display->addPagesFromDb();
		$ticket_display_js = "window.DESKPRO_TICKET_DISPLAY = " . $ticket_display->compileJs() . ";";

		$departments = App::getEntityRepository('DeskPRO:Department')->findAll();
		$ticket_categories = App::getEntityRepository('DeskPRO:TicketCategory')->findAll();

		return $this->render('UserBundle:NewTicket:new-ticket.html.twig', array(
			'departments' => $departments,
			'ticket_categories' => $ticket_categories,

			'ticket_options' => $newticket_formtype->getTicketOptions(),
			'newticket_formtype' => $newticket_formtype,
			'custom_fields' => $newticket_formtype->getTicketFields(),
			'form' => $form->createView(),
			'custom_fields' => $custom_fields,
			'ticket_display_js' => $ticket_display_js,
		));
    }

	################################################################################
	# thanks
	################################################################################

	public function thanksAction($ticket_ref)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);

		// Must exist, and match the ref n the session (so theres no info leak)
		if (!$ticket OR $ticket['ref'] != $this->session->get('submitted_ticket')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->render('UserBundle:NewTicket:thanks.html.twig', array(
			'ticket' => $ticket
		));
	}
}
