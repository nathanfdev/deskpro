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
use \Application\DeskPRO\EmailGateway\Ticket\SubjectMatchDetector;

class TicketGateway extends AbstractGateway
{
	public function run()
	{
		$person_processor = new PersonFromEmailProcessor();

		#-------------------------
		# Run detectors to see if its a reply
		#-------------------------

		$ticket = null;
		if (App::getSetting('core_tickets.gateway_catchall')) {
			$detector = new ToEmailTicketDetector(App::getSetting('core_tickets.gateway_catchall'));
			$ticket = $detector->findExistingTicket($this->reader);
		}

		if (!$ticket) {
			$detector = new CodeTicketDetector();
			$ticket = $detector->findExistingTicket($this->reader);
		}

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

		// The detectors above use TAC's, but they might
		// exist for people who are no longer on tickets
		// so we need to confirm that user parts are still
		// participants
		if ($ticket AND $person AND !$person['is_agent']) {
			if (!$ticket->hasParticipant($person)) {
				$person = null;
			}
		}

		if ($ticket AND $person) {
			$person_processor->passPerson($this->reader->getFromAddress(), $person);
			if ($person['is_agent']) {
				return $this->runNewAgentReply($ticket, $person);
			} else {
				return $this->runNewUserReply($ticket, $person);
			}
		} else {
			$person = $person_processor->findPerson($this->reader->getFromAddress());
			if ($person) {
				$person_processor->passPerson($this->reader->getFromAddress(), $person);
			} else {
				$person = $person_processor->createPerson($this->reader->getFromAddress());
			}

			return $this->runNewTicket($person);
		}
	}

	protected function doNewReply($ticket, $person)
	{
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

		$ticket->addMessage($messagE);

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}

		App::getOrm()->transactional(function($em) use ($ticket, $person) {
			$em->persist($ticket);
			$em->persist($person);
			$em->flush();
		});

		return $message;
	}

	public function handleCc($ticket, array $ccs)
	{
		App::getOrm()->beginTransaction();

		foreach ($ccs as $cc) {
			$person_processor = new PersonFromEmailProcessor();

			$cc_person = $person_processor->findPerson($cc);
			if ($cc_person) {
				$person_processor->passPerson($cc, $cc_person);
			} else {
				$cc_person = $person_processor->createPerson($cc);
			}

			App::getOrm()->persist($cc_person);
			App::getOrm()->flush();

			if (!$ticket->hasParticipant($cc_person)) {
				$ticket->addParticipant($cc_person);
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
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

		$newticket->ticket->subject = $this->reader->getSubject()->subject;
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