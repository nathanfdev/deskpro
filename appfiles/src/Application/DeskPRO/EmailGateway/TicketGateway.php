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
use \Application\DeskPRO\EmailGateway\Ticket\CodeTicketDetector;
use \Application\DeskPRO\EmailGateway\Ticket\ToEmailTicketDetector;
use \Application\DeskPRO\EmailGateway\Ticket\InReplyToDetector;

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

		if (!$ticket) {
			// Try to find it from In-Reply-To
			$detector = new InReplyToDetector();
			$ticket = $detector->findExistingTicket($this->reader);
		}

		if (!$ticket) {
			// Finally try a subject match
			$detector = new SubjectMatchDetector();
			$ticket = $detector->findExistingTicket($this->reader);
		}

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
				$person = $person_processor->createPerson();
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

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}

		return $message;
	}

	public function handleCc($ticket, array $ccs)
	{
		App::getOrm()->beginTransaction();

		foreach ($ccs as $cc) {
			$person_processor = new PersonFromEmailProcessor();
			// TODO change processor to accept email obj

			$cc_person = $person_processor->findPerson($cc);
			if ($cc_person) {
				$person_processor->passPerson($cc_person);
			} else {
				$cc_person = $person_processor->createPerson($cc);
			}

			App::getOrm()->persist($cc_person);
			App::getOrm()->flush();

			if (!$ticket->hasParticipant($cc_person)) {
				$ticket->addParticipant($cc_person);
			}
		}

		App::getOrm()->commit();
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

	protected function runNewTicket(Entity\Person $person)
	{
		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_GATEWAT_PERSON,
			$person
		);

		$newticket->ticket->subject = $this->reader->getSubject();
		if ($this->reader->getBodyText()->getBody()) {
			$newticket->ticket->message = $this->reader->getBodyHtml()->getBody();
		} else {
			$newticket->ticket->message = nl2br(htmlspecialchars($this->reader->getBodyText()->getBody(), ENT_QUOTES, 'UTF-8'));
		}

		$dep_id = App::getDb()->fetchColumn("SELECT id FROM departments WHERE parent_id IS NULL ORDER BY title ASC LIMIT 1");
		$newticket->ticket->department_id = $dep_id;

		App::getOrm()->beginTransaction();
		$ticket = $newticket->save();

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}
		App::getOrm()->commit();

		return $ticket;
	}
}