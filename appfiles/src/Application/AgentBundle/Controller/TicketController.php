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

		$person_inner_tab = $this->forward('AgentBundle:Person:view', array('person_id' => $ticket['person_id']))->getContent();

		return $this->render('AgentBundle:Ticket:view.twig', array(
			'person_inner_tab' => $person_inner_tab,
			'ticket' => $ticket
		));
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