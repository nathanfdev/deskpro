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
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;

class ProcessReply extends ProcessAbstract
{
	/**
	 * @var TicketIncomingEmail
	 */
	protected $ticket_email;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	protected $reader;

	/**
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * @param Ticket $ticket
	 * @param Person $person
	 * @param TicketIncomingEmail $ticket_email
	 */
	public function __construct(Ticket $ticket, Person $person, TicketIncomingEmail $ticket_email)
	{
		$this->ticket       = $ticket;
		$this->person       = $person;
		$this->ticket_email = $ticket_email;
		$this->reader       = $ticket_email->reader;
		$this->cleaner      = App::get('deskpro.core.input_cleaner');
	}

	/**
	 * @param string $context
	 * @return \Application\DeskPRO\Mail\Message|mixed|null|TicketMessage
	 * @throws \Exception
	 */
	public function run($context = 'user')
	{
		$this->logMessage("doNewRelpy context $context");
		
		$this->processBlobs();

		if ($context == 'user') {
			$executor_context = $this->getTicketManager()->createUserExecutorContext(
				$this->person,
				'newreply',
				'email'
			);
		} else {
			$executor_context = $this->getTicketManager()->createAgentExecutorContext(
				$this->person,
				'newreply',
				'email'
			);
		}

		$executor_context->setEmailContext($this->reader);

		if ($this->logger) {
			$orb_logger_adapter = new OrbLoggerAdapterHandler($this->logger);
			$executor_context->getLogger()->pushHandler($orb_logger_adapter);
		}

		//TODO
		$executor_context->getVars('reply_actions_override', $this->ticket_email->reply_actions);

		// If this was a reply via a TAC, then the person detected via address and the person who owns the TAC
		// should be the sames. Otherwise, *probably* means the agent used a different email address.
		if ($this->ticket_email->tac_person && $this->ticket_email->tac_person->is_agent && $this->ticket_email->tac_person->getId() != $this->person->getId()) {
			$this->logMessage('doNewRelpy agent reply with TAC from unknown email address');
			$this->setError('auth_invalid');

			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_agent:error-unknown-from.html.twig', array(
				'ticket'  => $this->ticket,
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			App::getMailer()->send($message);

			return null;
		}

		if ($this->ticket_email->is_dp3_reply) {
			$email_info = new TicketIncomingEmailMessageV3($this->ticket, $this->ticket_email, $this->cleaner, null);
		} else {
			$email_info = new TicketIncomingEmailMessage(
				$this->ticket,
				$this->ticket_email,
				$this->cleaner,
				array($this, 'replaceInlineAttachTokens')
			);
		}

		if (App::getSetting('core_tickets.gateway_agent_require_marker') && $context == 'agent' && !$email_info->found_top_marker) {
			// The marker is required for agent emails
			$this->logMessage('doNewRelpy agent reply missing marker');
			$this->setError('missing_marker');

			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_agent:error-marker-missing.html.twig', array(
				'ticket'  => $this->ticket,
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			App::getMailer()->send($message);

			return null;
		}

		if ($this->ticket_email->is_bounce) {
			$executor_context->getVars()->set('is_bounce_message', true);
		}

		$message = new TicketMessage();
		$message->email_reader = $this->reader;
		if ($this->reader->hasProperty('email_source')) {
			$message['email_source'] = $this->reader->getProperty('email_source');
		}

		if ($this->person->is_agent) {
			$message->creation_system = 'gateway.agent';
		} else {
			$message->creation_system = 'gateway.person';
		}

		$message['ticket'] = $this->ticket;
		$message['person'] = $this->person;
		$message['email'] = $this->reader->getFromAddress()->getEmail();

		$message['message'] = $email_info->body;
		$message['message_full'] = $email_info->body_full;
		$message['message_raw'] = $email_info->body_raw;

		$message['show_full_hint'] = false;
		$inline_reply_detector = new DetectInlineReply(App::getOrm(), $this->reader);
		if ($this->getLogger()) {
			$inline_reply_detector->setLogger($this->getLogger());
		}

		if ($inline_reply_detector->hasDifferentMessage() && $message['message_full']) {
			$message['show_full_hint'] = true;
		}

		if (isset($this->ticket_email->reply_actions['is_note'])) {
			$message['is_agent_note'] = true;
			
			//TODO
			$this->ticket->email_reader_action = 'agent_note';
		}

		$ticket_attach = array();
		foreach ($this->processBlobs() as $blob) {

			if (isset($this->dupe_inline_blobs[$blob->getId()])) {
				continue;
			}

			$attach = new TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			if (isset($this->inline_blobs[$blob->getId()])) {
				$attach->is_inline = true;
			}

			$message->addAttachment($attach);
			$ticket_attach[] = $attach;
		}

		$has_message = true;
		if (!$ticket_attach && !trim(strip_tags($email_info->body))) {
			$has_message = false;
		}

		$has_reply_codes = false;
		if ($this->ticket_email->reply_actions) {
			$has_reply_codes = true;
		}

		$message->message_hash = null;
		$message->initHashCode();

		// - Only add the message if we have an actual message
		// This allows email replies with action codes but no reply,
		// so the "empty reply" isnt processed as a reply
		$did_add_message = false;
		if (!isset($this->ticket_email->reply_actions['no_reply']) && ($has_message || ($has_reply_codes && !$has_message))) {

			$this->logMessage('[TicketGatewayProcessor] Checking for dupe message: ' . $message->getMessageHash());

			$did_add_message = true;
			if ($dupe_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $this->ticket, 10800, $this->getLogger())) {
				$this->setError('duplicate_message');
				$this->logMessage('[TicketGatewayProcessor] doNewReply duplicate message ' . $dupe_message->getId());

				// Reset some objects so they dont get flushed during next loop
				App::getOrm()->detach($this->ticket);
				App::getOrm()->detach($message);

				foreach ($ticket_attach as $a) {
					$a->ticket = null;
					$a->message = null;
					App::getOrm()->detach($a);
				}

				return $dupe_message;
			}

			$this->ticket->addMessage($message);
		} else {
			if (isset($this->ticket_email->reply_actions['no_reply'])) {
				$this->logMessage('No reply because of #noreply tag');
			} else {
				$this->logMessage('No reply because empty reply');
			}
		}

		if ($this->reader->getCcAddresses() || count($this->reader->getToAddresses()) > 1) {
			$this->logMessage('[TicketGatewayProcessor] Has CC');
			$this->handleCc($this->ticket, $this->reader->getDeliveredAddresses());
		}

		#------------------------------
		# Reply actions
		#------------------------------

		if ($this->ticket_email->reply_actions) {
			$reply_actions_apply = new ReplyActionsApplicator($this->ticket_email->reply_actions, App::getContainer());
			$reply_actions_context = new ReplyActionsContext();
			$reply_actions_context->ticket = $this->ticket;
			if ($did_add_message) {
				$reply_actions_context->message = $message;
			}
			$reply_actions_apply->apply($reply_actions_context);
		}

		#------------------------------
		# Default switch status
		#------------------------------

		if (!$this->ticket_email->is_bounce && !$message->is_agent_note && $did_add_message && !isset($this->ticket_email->reply_actions['status'])) {
			if ($this->person['is_agent'] && $context == 'agent') {
				$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_user');
				$this->ticket['status'] = Ticket::STATUS_AWAITING_USER;
			} else {
				$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_agent');
				$this->ticket['status'] = Ticket::STATUS_AWAITING_AGENT;
			}
		}

		App::getDb()->beginTransaction();

		try {
			App::getOrm()->persist($this->person);
			App::getOrm()->persist($this->ticket);
			if ($did_add_message) {
				App::getOrm()->persist($message);
			}

			$this->getTicketManager()->saveTicket($this->ticket, $executor_context);
			App::getOrm()->flush();

			if ($email_info->charset_error) {
				App::getOrm()->getConnection()->insert('tickets_messages_raw', array(
					'message_id' => $message['id'],
					'raw'        => $email_info->body,
					'charset'    => $email_info->charset_error,
				));
			}
			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

		return $message;
	}
}