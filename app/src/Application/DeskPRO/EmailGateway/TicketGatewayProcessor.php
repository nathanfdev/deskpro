<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;
use Application\DeskPRO\EmailGateway\Ticket\BounceDetector;
use Application\DeskPRO\EmailGateway\Ticket\CodeTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\CompositeDetector;
use Application\DeskPRO\EmailGateway\Ticket\Dp3Detector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectMatchDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectRefMatchDetector;
use Application\DeskPRO\EmailGateway\TicketGateway\AgentReplyCodes;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessAgentFwd;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessNew;
use Application\DeskPRO\EmailGateway\TicketGateway\ProcessReply;
use Application\DeskPRO\EmailGateway\TicketGateway\TicketIncomingEmail;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;

class TicketGatewayProcessor extends AbstractGatewayProcessor
{
	/**
	 * @var string
	 */
	protected $error_type;

	/**
	 * @var string
	 */
	protected $error;

	/**
	 * @var string
	 */
	protected $source_info;

	/**
	 * @var string
	 */
	protected $created_object_type;

	/**
	 * @var int
	 */
	protected $created_object_id;


	/**
	 * @return \Application\DeskPRO\Entity\Ticket|\Application\DeskPRO\Entity\TicketMessage|null
	 * @throws \Exception
	 */
	public function run()
	{
		#-------------------------
		# Run detectors to see if its a reply
		#-------------------------

		$ticket = null;
		$person = null;
		$tac_person = null;
		$is_bounce  = false;
		$is_dp3_reply = false;

		$can_add_new_person = false;

		$bounce_detector = new BounceDetector($this->reader, $this->getEm());
		$bounce_detector->setLogger($this->logger);

		if ($bounce_detector->isBounced()) {
			$is_bounce = true;

			$this->logMessage("Is bounced");
			$ticket	= $bounce_detector->getGuessedTicket();
			$can_add_new_person = true;
		}

		if (!$ticket) {
			$ticket_detect = $this->createTicketDetector();
			if ($is_bounce) {
				$ticket_detect->enableBouncedMode();
			}

			$ticket = $ticket_detect->findExistingTicket($this->reader);
			$this->logMessage("[TicketGatewayProcessor] Ticket Detector -- Ticket: " . ($ticket ? $ticket->id : 'none'));

			$tac_person = $ticket_detect->findTacPerson($this->reader);
			$this->logMessage("[TicketGatewayProcessor] Ticket Detector -- TAC Person: " . ($tac_person ? $tac_person->id : 'none'));

			if ($ticket) {
				$person = $ticket_detect->findExistingPerson($ticket, $this->reader);
				$this->logMessage("[TicketGatewayProcessor] Ticket Detector -- Person: " . ($person ? $person->id : 'none'));

				$can_add_new_person = $ticket_detect->canAddUnknownPerson($ticket, $this->reader);

				if ($ticket_detect->getMatchedDetector($this->reader) instanceof Dp3Detector) {
					$is_dp3_reply = true;
				}
			}
		}

		#-------------------------
		# Check if we should create a new user on the ticket
		#-------------------------

		if ($ticket AND !$person AND $can_add_new_person) {
			$this->logMessage(sprintf('[TicketGatewayProcessor] Could not find user on ticket, adding user with email %s', $this->reader->getFromAddress()->getEmail()));

			$person_processor = new PersonFromEmailProcessor();

			// If the detector didnt find a person, doesnt mean they dont exist
			$person = $person_processor->findPerson($this->reader->getFromAddress());

			// But we'll create them now if they dont
			if (!$person) {
				$this->logMessage('[TicketGatewayProcessor] No existing person found, will try and create it');
				$person = $person_processor->createPerson($this->reader->getFromAddress());
			}

			if ($person && !$person->is_agent) {
				$ticket->addParticipantPerson($person);
			}
		}

		#-------------------------
		# Reject agent bounces
		#-------------------------

		if ($person && $person->is_agent && $is_bounce) {
			$this->logMessage('[TicketGatewayProcessor] Is an agent message and is detected as bounced. Rejecting message.');
			$this->error = 'agent_bounce';
			$this->error_type = 'rejected';
			return null;
		}

		#-------------------------
		# Reject new tickets by disabled users
		#-------------------------

		if ($person && $person->is_disabled && !$ticket) {
			$this->logMessage('[TicketGatewayProcessor] User is disabeld, rejecting message');
			$this->error = 'from_disabled_user';
			$this->error_type = 'rejected';
			return null;
		}

		#-------------------------
		# Reject new replies by disabled users
		#-------------------------

		if ($person && (!$person->is_agent && $person->is_disabled)) {
			// user is disabled so can't create/reply to tickets
			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_user:account-disabled.html.twig', array(
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name' => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			$this->container->getMailer()->send($message);

			return null;
		}

		#-------------------------
		# Handle reply to resolved tickets
		#-------------------------

		$reply_as_new = false;

		if ($ticket AND $person AND !$person->is_agent AND $ticket->status == 'resolved' AND !$person->hasPerm('tickets.reopen_resolved')) {

			$this->logMessage('[TicketGatewayProcessor] Ticket is resolved');

			if ($person->hasPerm('tickets.reopen_resolved_createnew')) {
				$this->logMessage('[TicketGatewayProcessor] Has perm reopen_resolved_createnew so creating a new ticket');
				$ticket = null;
				$reply_as_new = true;

			} else {
				$this->logMessage('[TicketGatewayProcessor] Message is being rejected because ticket is resolved');

				if ($this->account_email_address) {
					$email_to = $this->account_email_address;
				} else {
					$email_to = $this->account->address;
				}

				$from_address = $this->container->getMailer()->getEmailAccountForTicket($ticket)->address;

				// user is disabled so can't create/reply to tickets
				$message = $this->container->getMailer()->createMessage();
				$message->setTemplate('DeskPRO:emails_user:new-reply-reject-resolved.html.twig', array(
					'subject'  => $this->reader->getSubject()->getSubjectUtf8(),
					'name'     => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
					'ticket'   => $ticket,
					'person'   => $person,
					'email_to' => $email_to
				));
				$message->setTo($this->reader->getFromAddress()->getEmail());
				$message->setFrom($from_address);
				$message->attach(\Swift_Attachment::newInstance(
					$this->reader->getRawSource(),
					'message.eml',
					'message/rfc822'
				));
				App::getMailer()->send($message);

				$this->error = 'obj_closed';
				$this->error_type = 'rejected';
				return null;
			}
		}

		#-------------------------
		# Detect agent reply codes
		#-------------------------

		$reply_actions   = null;
		$email_body_html = $this->reader->getBodyHtml()->getBodyUtf8();
		$email_body_text = $this->reader->getBodyText()->getBodyUtf8();

		if ($person) {
			$check_person = $person;
		} elseif ($this->reader->getFromAddress()) {
			$check_person = App::getDataService('Agent')->getByEmail($this->reader->getFromAddress()->getEmail());
		} else {
			$check_person = null;
		}

		if ($check_person && $check_person->is_agent) {
			if ($email_body_html) {
				$this->logMessage("Checking for agent reply codes in HTML body");
				$rc = new AgentReplyCodes($email_body_html, true);
				$rc->setCleaner($this->container->getInputCleaner());
				$rc->setLogger($this->logger);

				$reply_actions = $rc->getProperties();
				if ($reply_actions) {
					$email_body_html = $rc->getNewBody();
				}
			} else {
				$this->logMessage("Checking for agent reply codes in TEXT body");
				$rc = new AgentReplyCodes($email_body_text, false);
				$rc->setLogger($this->logger);

				$reply_actions = $rc->getProperties();
				if ($reply_actions) {
					$email_body_text = $rc->getNewBody();
				}
			}
		}

		#-------------------------
		# Unset ticket if its an agent fwd
		#-------------------------

		if ($ticket AND App::getSetting('core_tickets.process_agent_fwd') AND $person['is_agent'] AND ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
			$this->logMessage(sprintf("Found a ticket match #%d but this is an agent fwd so unsetting", $ticket->getId()));
			$ticket = null;
		}

		#-------------------------
		# Create ticket email obj
		#-------------------------

		$ticket_email = new TicketIncomingEmail($this);
		$ticket_email->reader          = $this->reader;
		$ticket_email->ticket          = $ticket;
		$ticket_email->person          = $person;
		$ticket_email->tac_person      = $tac_person;
		$ticket_email->is_bounce       = $is_bounce;
		$ticket_email->email_body_html = $email_body_html;
		$ticket_email->email_body_text = $email_body_text;
		$ticket_email->is_dp3_reply    = $is_dp3_reply;
		$ticket_email->reply_actions   = $reply_actions;

		if ($ticket && $person) {
			App::$container->getTicketManager()->markAsManaged($ticket);
			return $this->runReply($ticket_email);
		} else {
			return $this->runNew($ticket_email, $reply_as_new);
		}
	}


	/**
	 * @param TicketIncomingEmail $ticket_email
	 * @return \Application\DeskPRO\Entity\TicketMessage|null
	 */
	private function runReply(TicketIncomingEmail $ticket_email)
	{
		$ticket = $ticket_email->ticket;
		$person = $ticket_email->person;

		$person_processor = new PersonFromEmailProcessor();
		$person_processor->passPerson($this->reader->getFromAddress(), $person);

		if ($this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()) {
			$this->logMessage('[TicketGatewayProcessor] Detected a DeskPRO reply, disabling disable_autoresponses');
			$person->setDisableAutoresponses(
				true,
				'User detected as a DeskPRO helpdesk'
			);
		}

		if (!$person->disable_autoresponses) {
			if ($return_path = $this->reader->getHeader('Return-Path')) {
				if ($return_path->getHeader() == '<>') {
					$this->logMessage("Null return path, disabling auto-responses for this user");
					$person->setDisableAutoresponses(
						true,
						'Client sent a null Return-Path'
					);
				}
			}
		}

		App::setCurrentPerson($person);

		// If the agent is replying to an email that is not a notification, then this check doesnt
		// need to run (e.g., they replied to an email they were CCd on).
		$is_reply_to_dpmail = false;
		if ($body_html = $ticket_email->email_body_html) {
			if (
				strpos($body_html, 'DP_BOTTOM_MARK') !== false
				|| strpos($body_html, 'DP_TOP_MARK') !== false
				|| strpos($body_html, 'DP_MESSAGE_BEGIN') !== false
				|| strpos($body_html, 'DP_USER_EMAIL') !== false
				|| strpos($body_html, 'DP_AGENT_EMAIL') !== false
			) {
				$is_reply_to_dpmail = true;
			}
		}

		if ($is_reply_to_dpmail) {
			$this->logMessage('[TicketGatewayProcessor] IS a reply to a DeskPRO email');
		} else {
			$this->logMessage('[TicketGatewayProcessor] NOT a reply to a DeskPRO email');
		}

		// todo injection
		$translator = App::$container->getTranslator();
		$reply_proc = new ProcessReply($ticket, $person, $ticket_email, $translator);
		$reply_proc->setLogger($this->logger);

		if (
			$person['is_agent']
			&& (
				// Is not a user email
				(strpos($ticket_email->email_body_html, 'DP_USER_EMAIL') === false && $is_reply_to_dpmail)
				||
				// Or is a text email where user email markers wouldnt be detected
				($ticket_email->tac_person && $ticket_email->tac_person->getId() == $person->getId() && !$is_reply_to_dpmail)
			)
		) {
			$this->logMessage('[TicketGatewayProcessor] runNewAgentReply');
			$obj = $reply_proc->run('agent');
		} else {
			$this->logMessage('[TicketGatewayProcessor] runNewUserReply');
			$obj = $reply_proc->run('user');
		}

		if ($err = $reply_proc->getError()) {
			$this->error = $err;
			$this->error_type = $reply_proc->getErrorType();
			return null;
		}

		$this->created_object_type = 'ticket_message';
		$this->created_object_id   = $obj->id;

		return $obj;
	}


	/**
	 * @param TicketIncomingEmail $ticket_email
	 * @param bool $reply_as_new
	 * @return \Application\DeskPRO\Entity\Ticket|null
	 */
	private function runNew(TicketIncomingEmail $ticket_email, $reply_as_new = false)
	{
		$ticket = $ticket_email->ticket;
		$person = $ticket_email->person;

		$this->logMessage('[TicketGatewayProcessor] Creating new ticket');

		$person_processor = new PersonFromEmailProcessor();
		$person = $person_processor->findPerson($this->reader->getFromAddress());

		if ($person) {
			$this->logMessage('[TicketGatewayProcessor] Found existing person: ' . $person['id']);
			$person_processor->passPerson($this->reader->getFromAddress(), $person);
		} else {
			if ($this->container->getSetting('core.reg_enabled')) {
				$person = $person_processor->createPerson($this->reader->getFromAddress());
				$this->logMessage('[TicketGatewayProcessor] Created new contact: ' . $person['id']);
			}
		}

		// Still no person means reg is closed
		if (!$person) {
			$this->logMessage('[TicketGatewayProcessor] No user and closed registration');
			$this->error = EmailSource::ERR_PERM_INSUFFICIENT;
			$this->error_type = 'rejected';

			$account_manager = App::$container->getEmailAccountManager();
			$user_email = $this->reader->getFromAddress()->getEmail();

			if (!$ticket_email->is_bounce && !$this->reader->isFromRobot() && !$account_manager->findAccountForEmailAddress($user_email)) {
				$message = $this->container->getMailer()->createMessage();
				$message->setTemplate('DeskPRO:emails_user:new-ticket-reg-closed.html.twig', array(
					'subject' => $this->reader->getSubject()->getSubjectUtf8(),
					'name' => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
				));
				$message->setTo($this->reader->getFromAddress()->getEmail());
				$this->container->getMailer()->send($message);
				return null;
			}
		}

		if ($person) {
			$this->logMessage('[TicketGatewayProcessor] Found existing person: ' . $person['id']);
			$person_processor->passPerson($this->reader->getFromAddress(), $person);
		} else {
			$this->logMessage('[TicketGatewayProcessor] Creating new contact');

		}

		if ($person && $person->is_agent && $ticket_email->is_bounce) {
			$this->logMessage('[TicketGatewayProcessor] Is an agent message and is detected as bounced. Rejecting message.');
			$this->error = 'agent_bounce';
			$this->error_type = 'rejected';
			return null;
		}

		if ($this->reader->getHeader('X-DeskPRO-Build') && $this->reader->getHeader('X-DeskPRO-Build')->getHeader()) {
			$this->logMessage('[TicketGatewayProcessor] Detected a DeskPRO reply, disabling disable_autoresponses');
			$person->setDisableAutoresponses(
				true,
				'User detected as a DeskPRO helpdesk'
			);
		}

		if (!$person->disable_autoresponses) {
			if ($return_path = $this->reader->getHeader('Return-Path')) {
				if ($return_path->getHeader() == '<>') {
					$this->logMessage("Null return path, disabling auto-responses for this user");
					$person->setDisableAutoresponses(
						true,
						'Client sent a null Return-Path'
					);
				}
			}
		}

		App::setCurrentPerson($person);

		if ($this->container->getSetting('core_tickets.process_agent_fwd') AND $person->is_agent AND ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
			$this->logMessage('[TicketGatewayProcessor] runNewForwardedTicket');

			$fwd_proc = new ProcessAgentFwd($this->account, $person, $ticket_email);
			$fwd_proc->setLogger($this->logger);

			$obj = $fwd_proc->run();

			if ($err = $fwd_proc->getError()) {
				$this->error = $err;
				$this->error_type = 'rejected';
				return null;
			}

			$this->created_object_type = 'ticket';
			$this->created_object_id   = $obj->id;

			return $obj;
		} else {
			$this->logMessage('[TicketGatewayProcessor] runNewTicket');

			$ticket_email->force_reply_cutter = $reply_as_new;

			// todo injection
			$translator = App::$container->getTranslator();
			$new_proc = new ProcessNew($this->account, $person, $ticket_email, $translator);
			$new_proc->setLogger($this->logger);

			$obj = $new_proc->run();

			if ($err = $new_proc->getError()) {
				$this->error = $err;
				$this->error_type = $new_proc->getErrorType();
				return null;
			}

			$this->created_object_type = 'ticket';
			$this->created_object_id   = $obj->id;

			return $obj;
		}
	}


	/**
	 * @return CompositeDetector
	 */
	private function createTicketDetector()
	{
		$ticket_detect = new CompositeDetector();
		$ticket_detect->setLogger($this->logger);

		$ticket_detect->addDetector(new CodeTicketDetector());

		if ($this->container->getSetting('core.deskpro3importer')) {
			$ticket_detect->addDetector(new Dp3Detector());
		}

		$ticket_detect->addDetector(new SubjectRefMatchDetector());

		if ($this->container->getSetting('core_tickets.gateway_enable_subject_match')) {
			$ticket_detect->addDetector(new SubjectMatchDetector());
		}

		return $ticket_detect;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getErrorCode()
	{
		return $this->error;
	}


	/**
	 * 'error' or 'rejected'
	 * @return string
	 */
	public function getErrorType()
	{
		return $this->error_type;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getSourceInfo()
	{
		if ($this->source_info) {
			$messages = is_array($this->source_info) ? $this->source_info : array($this->source_info);
		} else {
			$messages = array();
		}

		return $messages;
	}


	/**
	 * @return int
	 */
	public function getCreatedObjectId()
	{
		return $this->created_object_id;
	}

	/**
	 * @return string
	 */
	public function getCreatedObjectType()
	{
		return $this->created_object_type;
	}
}