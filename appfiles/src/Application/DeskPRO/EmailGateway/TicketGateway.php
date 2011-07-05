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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EmailGateway\AbstractGateway;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Ticket\CodeTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\ToEmailTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\InReplyToDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectMatchDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectRefMatchDetector;
use Application\DeskPRO\EmailGateway\Cutter\CutterDefFactory;
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;

class TicketGateway extends AbstractGateway
{
	const EVENT_EVENT                    = 'DeskPRO_onTicketGatewayInit';
	const EVENT_BEFORE_RUN_ACTION        = 'DeskPRO_onBeforeTicketGatewayRunAction';
	const EVENT_RUN_ACTION               = 'DeskPRO_onTicketGatewayRunAction';
	const EVENT_BEFORE_NEWREPLY          = 'DeskPRO_onBeforeTicketGatewayNewReply';
	const EVENT_NEWREPLY                 = 'DeskPRO_onTicketGatewayNewReply';
	const EVENT_BEFORE_NEWTICKET         = 'DeskPRO_onBeforeTicketGatewayNewTicket';
	const EVENT_NEWTICKET                = 'DeskPRO_onTicketGatewayNewTicket';
	const EVENT_BEFORE_FWD_NEWTICKET     = 'DeskPRO_onBeforeTicketGatewayNewFwdTicket';
	const EVENT_FWD_NEWTICKET            = 'DeskPRO_onTicketGatewayNewFwdTicket';

	/**
	 * @var \Application\DeskPRO\EmailGateway\Cutter\Def\Generic
	 */
	protected $cutterDef;

	protected function init()
	{
		$this->cutterDef = CutterDefFactory::getDef($this->reader);
	}

	public function run()
	{
		$person_processor = new PersonFromEmailProcessor();

		#-------------------------
		# Run detectors to see if its a reply
		#-------------------------

		$ticket = null;
		$person = null;
		
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
			// Try ref match
			$detector = new SubjectRefMatchDetector();
			$ticket = $detector->findExistingTicket($this->reader);
		}

		if (!$ticket) {
			// Finally try subject string match
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
			if ($ticket->person['id'] != $person['id'] AND !$ticket->hasParticipantPerson($person)) {
				$person = null;
			}
		}

		if ($ticket AND !$person AND $detector->canAddUnknownPerson()) {
			$person = Entity\Person::newContactPerson(array('email' => $this->reader->getFromAddress()));
			$ticket->addParticipantPerson($person);
		}

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'cancel' => false
		));
		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_RUN_ACTION, $ev);

		if ($ev->cancel) {
			return null;
		}

		$ret = null;
		if ($ticket AND $person) {
			$person_processor->passPerson($this->reader->getFromAddress(), $person);

			App::setCurrentPerson($person);

			if ($person['is_agent']) {
				$ret = $this->runNewAgentReply($ticket, $person);
			} else {
				$ret = $this->runNewUserReply($ticket, $person);
			}
		} else {
			$person = $person_processor->findPerson($this->reader->getFromAddress());
			if ($person) {
				$person_processor->passPerson($this->reader->getFromAddress(), $person);
			} else {
				$person = $person_processor->createPerson($this->reader->getFromAddress());
			}

			App::setCurrentPerson($person);

			if ($person['is_agent'] AND ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
				$ret = $this->runNewForwardedTicket($person);
			} else {
				$ret = $this->runNewTicket($person);
			}
		}

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'return' => $ret
		));
		$this->event_dispatcher->dispatch(self::EVENT_RUN_ACTION, $ev);

		return $ret;
	}

	protected function doNewReply($ticket, $person)
	{
		$email_info = array();
		$email_info['subject'] = $this->reader->getSubject()->subject;
		if ($this->reader->getBodyHtml()->getBody()) {
			$email_info['body'] = $this->reader->getBodyHtml()->getBody();
			$email_info['body_is_html'] = true;
		} else {
			$email_info['body'] = nl2br(htmlspecialchars($this->reader->getBodyText()->getBody(), ENT_QUOTES, 'UTF-8'));
			$email_info['body_is_html'] = false;
		}

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'email_info' => $email_info,
			'cancel' => false,
		));
		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_NEWREPLY, $ev);

		if ($ev->cancel) {
			return null;
		}

		$email_info = $ev->email_info;

		$message = new Entity\TicketMessage();
		$message['ticket'] = $ticket;
		$message['person'] = $person;

		$message['message'] = $email_info['body'];

		foreach ($this->processBlobs() as $blob) {
			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$message->addAttachment($attach);
		}

		$ticket->addMessage($message);

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}

		if ($person['is_agent']) {
			$ticket['status'] = Entity\Ticket::STATUS_PENDING;
		} else {
			$ticket['status'] = Entity\Ticket::STATUS_OPEN;
		}

		App::getOrm()->transactional(function($em) use ($ticket, $person) {
			$em->persist($ticket);
			$em->persist($person);
			$em->flush();
		});

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'message' => $message
		));
		$this->event_dispatcher->dispatch(self::EVENT_NEWREPLY, $ev);

		return $message;
	}

	public function handleCc($ticket, array $ccs)
	{
		App::getOrm()->beginTransaction();

		foreach ($ccs as $cc) {
			$person_processor = new PersonFromEmailProcessor();

			$cc_person = $person_processor->findPerson($cc);
			if (!$cc_person) {
				$cc_person = $person_processor->createPerson($cc);
			}

			if (!$cc_person) {
				continue;
			}

			App::getOrm()->persist($cc_person);
			App::getOrm()->flush();

			if (!$ticket->hasParticipantPerson($cc_person)) {
				$ticket->addParticipantPerson($cc_person);
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
		$email_info = array();
		$email_info['subject'] = $this->reader->getSubject()->subject;
		if ($this->reader->getBodyHtml()->getBody()) {
			$email_info['body'] = $this->reader->getBodyHtml()->getBody();
			$email_info['body_is_html'] = true;
		} else {
			$email_info['body'] = nl2br(htmlspecialchars($this->reader->getBodyText()->getBody(), ENT_QUOTES, 'UTF-8'));
			$email_info['body_is_html'] = false;
		}

		$ev = $this->createGatewayEvent(array(
			'person' => $person,
			'email_info' => $email_info,
			'cancel' => false,
		));
		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_NEWTICKET, $ev);

		if ($ev->cancel) {
			return null;
		}

		$email_info = $ev->email_info;

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_GATEWAT_PERSON,
			$person
		);

		$newticket->ticket->subject = $email_info['subject'];
		$newticket->ticket->message = $email_info['body'];

		$dep_id = App::getDb()->fetchColumn("SELECT id FROM departments WHERE parent_id IS NULL ORDER BY title ASC LIMIT 1");
		$newticket->ticket->department_id = $dep_id;

		App::getOrm()->beginTransaction();
		$ticket = $newticket->save();

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}
		App::getOrm()->commit();

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
		));
		$this->event_dispatcher->dispatch(self::EVENT_NEWTICKET, $ev);

		return $ticket;
	}


	############################################################################
	# New Ticket: Agent forwarded message
	############################################################################

	protected function runNewForwardedTicket(Entity\Person $agent)
	{
		#------------------------------
		# Read in email props and create cutter
		#------------------------------

		$email_info = array();
		$email_info['subject'] = $this->reader->getSubject()->subject;
		if ($this->reader->getBodyText()->getBody()) {
			$email_info['body'] = $this->reader->getBodyHtml()->getBody();
			$email_info['body_is_html'] = true;
		} else {
			$email_info['body'] = nl2br(htmlspecialchars($this->reader->getBodyText()->getBody(), ENT_QUOTES, 'UTF-8'));
			$email_info['body_is_html'] = false;
		}

		$fwd_cutter = new ForwardCutter($email_info['body'], $email_info['body_is_html'], $this->cutterDef);

		$ev = $this->createGatewayEvent(array(
			'email_info' => $email_info,
			'fwd_cutter' => $fwd_cutter,
			'cancel' => false,
		));
		
		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_FWD_NEWTICKET, $ev);

		if ($ev->cancel OR !$fwd_cutter->isValid()) {
			return null;
		}

		$email_info['subject'] = ForwardCutter::cutSubjectForwardPrefix($email_info['subject']);

		#------------------------------
		# Find person
		#------------------------------

		$person_processor = new PersonFromEmailProcessor();
		$person_email_item = $fwd_cutter->getUserEmailItem();

		$person = $person_processor->findPerson($person_email_item);
		if ($person) {
			$person_processor->passPerson($person_email_item, $person);
		} else {
			$person = $person_processor->createPerson($person_email_item, true);
		}

		#------------------------------
		# Create ticket
		#------------------------------

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_GATEWAT_PERSON,
			$person
		);
		$newticket->ticket->subject = $email_info['subject'];
		$newticket->ticket->message = strip_tags($fwd_cutter->getForwardedMessage());

		// TODO using strip tags until we have HTML tidy, the cutter
		// will most likely cut in the middle of a div etc that we need to clean

		App::getOrm()->beginTransaction();
		$ticket = $newticket->save();
		
		$ticket['agent'] = $agent;
		App::getOrm()->persist($ticket);
		
		App::getOrm()->commit();

		// Add agent reply if there was one
		$agent_reply = $fwd_cutter->getReply();
		if ($agent_reply) {

			App::getOrm()->beginTransaction();
			$agent_message = new \Application\DeskPRO\Entity\TicketMessage();
			$agent_message->person = $agent;
			$agent_message['message'] = strip_tags($agent_reply);

			$ticket->addMessage($agent_message);

			App::getOrm()->persist($ticket);
			App::getOrm()->commit();
		}

		return $ticket;
	}
}