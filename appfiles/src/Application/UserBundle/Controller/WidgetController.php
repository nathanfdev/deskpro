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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\UserBundle\Form\NewTicketType;

class WidgetController extends AbstractController
{
	public function overlayAction()
	{
		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$departments = App::getEntityRepository('DeskPRO:Department')->findAll();

		$vars = array(
			'departments' => $departments,
			'newticket' => $newticket,
			'newticket_formtype' => $newticket_formtype,
			'ticket_options' => $newticket_formtype->getTicketOptions(),
			'form' => $form->createView(),
		);

		return $this->render('UserBundle:Widget:overlay.html.twig', $vars);
	}

	public function newTicketAction()
	{
		$this->ensureRequestToken('newticket_widget');

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$form->bindRequest($this->get('request'));

		$newticket->ticket->attach_ids = $this->in->getCleanValueArray('attach_ids', 'string', 'discard');
		$newticket->ticket->attach_ids_authed = true;

		$validator = new \Application\UserBundle\Validator\NewTicketValidator();

		if ($validator->isValid($newticket)) {
			$ticket = $newticket->save();
			$person = $ticket['person'];

			return $this->createJsonResponse(array(
				'ticket_id' => $ticket->id,
				'email' => $newticket->person->email
			));
		} else {
			$errors = $validator->getErrors(true);
			$error_fields = $validator->getErrorGroups(true);

			return $this->createJsonResponse(array(
				'is_error' => true,
				'errors' => $error_fields
			));
		}
	}
}
