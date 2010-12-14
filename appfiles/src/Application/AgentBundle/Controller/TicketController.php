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

/**
 * Handles ticket searches
 */
class TicketController extends AbstractController
{
	public function viewAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$person_inner_tab = $this->forward('AgentBundle:Person:view', array('person_id' => $ticket['person_id']))->getContent();

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();

		$custom_fields_form = new \Symfony\Component\Form\FieldGroup('custom_fields');
		$custom_fields = array();
		foreach ($ticket_field_defs as $f_def) {
			$f = $f_def->getHandler()->getFormField();
			$custom_fields_form->add($f);


			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'form' => $f,
				'rendered' => ''
			);
		}

		return $this->render('AgentBundle:Ticket:view.twig', array(
			'person_inner_tab' => $person_inner_tab,
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
		));
	}

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

		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);
		$ticket_edit->save();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function ajaxSaveReplyAction($ticket_id)
	{
		$ticket = $this->getTicketOr404($ticket_id);
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);

		$message = new Entity\TicketMessage();
		$message['person'] = $this->person;
		$message['message'] = $this->in->getString('message');

		$ticket_edit->addMessage($message);
		$ticket_edit->save();

		return $this->render('AgentBundle:Ticket:ticket-message.twig', array(
			'message' => $message
		));
	}

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