<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\EmailGateway\AbstractGateway;
use \Application\DeskPRO\EmailGateway\Reader\AbstractReader;

class TicketGateway extends AbstractGateway
{
	public function run()
	{
		$person_processor = new PersonFromEmailProcessor($this->reader);

		#-------------------------
		# Run detectors to see if its a reply
		#-------------------------

		if (App::getSetting('core_tickets.gateway_catchall')) {
			$detector = new ToEmailTicketDetector(App::getSetting('core_tickets.gateway_catchall'));
		} else {
			$detector = new CodeTicketDetector();
		}

		$ticket = $detector->findExistingTicket($this->reader);
		if ($ticket) {
			$person = $detector->findExistingPerson($ticket, $this->reader);
		}


		#-------------------------
		# If we have aticket and user, run reply,
		# otherwise just make a new ticket
		#-------------------------

		if ($ticket AND $person) {
			$person_processor->passPerson($person);
			if ($person['is_agent']) {
				return $this->runNewAgentReply($ticket, $person);
			} else {
				return $this->runNewUserReply($ticket, $person);
			}
		} else {
			$person = $person_processor->findPerson();
			if ($person) {
				$person_processor->passPerson($person);
			} else {
				$person = $person_processor->findPerson();
			}

			return $this->runNewTicket($person);
		}
	}

	protected function doNewReply($ticket, $person)
	{
		$ticket_edit = App::getApi('tickets')->getTicketEditor($ticket);

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $person;

		if ($this->reader->getBodyHtml()->getBody()) {
			$message['message'] = $this->reader->getBodyHtml()->getBody();
		} else {
			$message['message'] = nl2br(htmlspecialchars($this->reader->getBodyText()->getBody(), ENT_QUOTES, 'UTF-8'));
		}

		foreach ($this->processBlobs() as $blob) {
			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$message->addAttachment($attach);
		}

		$ticket_edit->addMessage($message);

		$ticket_edit->save();

		return $message;
	}


	############################################################################
	# New Reply: Agent
	############################################################################

	protected function runNewAgentReply(Entity\Ticket $ticket, Entity\Person $person)
	{
		$message = $this->doNewReply($ticket, $person);
		return $message;
	}


	############################################################################
	# New Reply: User
	############################################################################

	protected function runNewUserReply(Entity\Ticket $ticket, Entity\Person $person)
	{
		$message = $this->doNewReply($ticket, $person);
		return $message;
	}


	############################################################################
	# New Ticket
	############################################################################

	protected function newTicket(Entity\Person $person)
	{
		#-------------------------
		# Create the ticket
		#-------------------------

		$ticket = new Entity\Ticket();
		$ticket['status']  = Entity\Ticket::STATUS_OPEN;
		$ticket['person']  = $person;
		$ticket['subject'] = $this->reader->getSubject()->getSubject();
		$ticket['creation_system'] = Entity\Ticket::CREATED_GATEWAT_PERSON;
		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		$message = $this->doNewReply($ticket, $person);

		return $message;
	}
}