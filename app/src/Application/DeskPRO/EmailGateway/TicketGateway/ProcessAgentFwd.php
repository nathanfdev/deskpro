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
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
use Application\DeskPRO\EmailGateway\Reader\Item\Attachment;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Orb\Util\Strings;

class ProcessAgentFwd extends ProcessAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $person;

	/**
	 * @var TicketIncomingEmail
	 */
	private $ticket_email;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount
	 */
	protected $account;

	/**
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * @param EmailAccount $account
	 * @param Person $person
	 * @param TicketIncomingEmail $ticket_email
	 */
	public function __construct(EmailAccount $account, Person $person, TicketIncomingEmail $ticket_email)
	{
		$this->account       = $account;
		$this->person        = $person;
		$this->ticket_email  = $ticket_email;
		$this->reader        = $ticket_email->reader;
		$this->cleaner       = App::get('deskpro.core.input_cleaner');
	}


	/**
	 * {@inheritDoc}
	 */
	public function run()
	{
		$this->logMessage('[TicketGatewayProcessor] Forwarded ticket by ' . $this->person->getId() . ' ' . $this->person->getDisplayContact());

		$executor_context = $this->getTicketManager()->createAgentExecutorContext(
			$this->person,
			'new',
			'email'
		);

		#------------------------------
		# Read in email props and create cutter
		#------------------------------

		$email_info = array();
		$email_info['subject'] = $this->reader->getSubject()->subject;
		if ($email_info['body'] = $this->ticket_email->email_body_text) {
			$email_info['body_is_html'] = false;
		} else {
			$email_info['body'] = $this->ticket_email->email_body_html;
			$email_info['body_is_html'] = false;
			$email_info['body'] = Strings::html2Text($email_info['body']);
		}

		$fwd_cutter = new ForwardCutter($email_info['body'], $email_info['body_is_html'], $this->cutterDef);

		if (!$fwd_cutter->isValid()) {
			$this->logMessage('[TicketGatewayProcessor] Invalid forward');

			$has_eml_attach = false;
			foreach ($this->reader->getAttachments() as $attach) {
				if ($attach->mime_type == 'message/rfc822' && $attach->file_name == 'email.eml') {
					$has_eml_attach = $attach;
					break;
				}
			}

			if ($has_eml_attach) {
				return $this->runNewForwardedEmailAsAttachTicket($has_eml_attach);
			}

			if ($fwd_cutter->getErrorCode() == 'unknown_email') {
				$this->setError(EmailSource::ERR_INVALID_FWD_EMAIL);
			} else {
				$this->setError(EmailSource::ERR_INVALID_FWD);
			}

			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_agent:error-invalid-forward.html.twig', array(
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
				'error'   => $this->error
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			$message->attach(\Swift_Attachment::newInstance(
				$this->reader->getRawSource(),
				'message.eml',
				'message/rfc822'
			));

			App::getMailer()->send($message);

			return null;
		}

		$email_info['subject'] = ForwardCutter::cutSubjectForwardPrefix($email_info['subject']);

		$agent_reply = $fwd_cutter->getReply();

		if ($agent_reply) {
			$agent_reply = $this->cleanBodyText($agent_reply);
		}

		#------------------------------
		# Find person
		#------------------------------

		$person_processor = new PersonFromEmailProcessor();
		$person_email_item = $fwd_cutter->getUserEmailItem();

		$user = $person_processor->findPerson($person_email_item);
		if ($user) {
			$person_processor->passPerson($person_email_item, $user);
		} else {
			$user = $person_processor->createPerson($person_email_item, true);
		}

		#------------------------------
		# Create the ticket
		#------------------------------

		$ticket = new Ticket();
		$ticket->subject       = $email_info['subject'];
		$ticket->person        = $user;
		$ticket->status        = 'awaiting_agent';
		$ticket->email_account = $this->account;
		$ticket->creation_system = 'gateway.agent';

		$ticket_message = new TicketMessage();
		$ticket_message->person = $user;

		$body = $fwd_cutter->getForwardedMessage();
		$body = $this->cleanBodyText($body);
		$ticket_message->setMessageText($body);

		if ($this->reader->getProperty('email_source')) {
			$ticket_message->email_source = $this->reader->getProperty('email_source');
		}

		$ticket->addMessage($ticket_message);

		// Add agent reply if there was one
		$agent_ticket_message = null;
		if ($agent_reply) {
			//TODO
			//$ticket->getTicketLogger()->recordExtra('is_fwd_reply', true);

			$this->logMessage('[TicketGatewayProcessor] Adding agent reply');
			$agent_reply = nl2br(htmlspecialchars($agent_reply, \ENT_QUOTES, 'UTF-8'));

			$agent_ticket_message = new TicketMessage();
			$agent_ticket_message->person = $this->person;
			$agent_ticket_message->setMessageHtml($agent_reply);
			$ticket->addMessage($this->person_message);
			$ticket->setStatus('awaiting_user');
		}

		foreach ($this->processBlobs() as $blob) {
			$attach = new TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			if (isset($this->inline_blobs[$blob->id])) {
				$attach->is_inline = true;
			}

			if ($agent_ticket_message) {
				$agent_ticket_message->addAttachment($attach);
			} else {
				$ticket_message->addAttachment($attach);
			}

			$blob->is_temp = false;
			App::getOrm()->persist($blob);
		}

		//TODO
		$tracker_extras = array(
			'fwd_via_agent' => $this->person
		);
		if ($this->person->getPref("agent_notify_override.forward.email")) {
			$tracker_extras['force_notify_email'] = array($this->person->id);
		}
		if ($this->person->getPref("agent_notify_override.forward.alert")) {
			$tracker_extras['force_notify_alert'] = array($this->person->id);
		}
		$fwd_info = $fwd_cutter->getData();
		if (!empty($fwd_info['fwd_cc_unknown'])) {
			$tracker_extras['fwd_cc_unknown'] = $fwd_info['fwd_cc_unknown'];
		}

		// Handle CC's
		$fwd_data = $fwd_cutter->getData();
		$cc_emails = $this->reader->getDeliveredAddresses();

		if ($fwd_data['fwd_cc_addresses']) {
			foreach ($fwd_data['fwd_cc_addresses'] as $e) {
				$e_a = new EmailAddress();
				$e_a->email = $e['email'];
				$e_a->name = $e['name'];

				$cc_emails[] = $e_a;
			}
		}

		#------------------------------
		# Process new ticket
		#------------------------------

		App::getDb()->beginTransaction();

		try {
			if ($cc_emails) {
				$this->handleCc($ticket, $cc_emails);
			}

			$this->getTicketManager()->saveTicket($ticket, $executor_context);
			$this->logMessage('[TicketGatewayProcessor] Created ticket ' . $ticket['id']);

			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

		return $ticket;
	}


	/**
	 * @param Attachment $has_eml_attach
	 * @return Ticket
	 * @throws \Exception
	 */
	private function runNewForwardedEmailAsAttachTicket(Attachment $has_eml_attach)
	{
		$executor_context = $this->getTicketManager()->createAgentExecutorContext(
			$this->person,
			'new',
			'email'
		);

		$user_raw_source = $has_eml_attach->getFileContents();
		$user_reader = new EzcReader();
		$user_reader->setRawSource($user_raw_source);
		$user_reader->setProperty('email_source', $user_raw_source);

		$this->logMessage('[TicketGatewayProcessor] Forwarded attached ticket by ' . $this->person->getId() . ' ' . $this->person->getDisplayContact());

		if ($this->reader->getBodyHtml() && $this->reader->getBodyHtml()->body_utf8) {
			$agent_reply = $this->reader->getBodyHtml()->body_utf8;

			$agent_reply = $this->cleaner->clean($agent_reply, 'html_email_preclean');
			$agent_reply = $this->cleaner->clean($agent_reply, 'html_email_basicclean');
			$agent_reply = $this->cleaner->clean($agent_reply, 'html_email');
			$agent_reply = $this->trimHtmlWhitespace($agent_reply);
			$agent_reply = $this->cleaner->clean($agent_reply, 'html_email_postclean');

		} else {
			$agent_reply = trim($this->reader->getBodyText()->body_utf8);
			if ($agent_reply) {
				$agent_reply = nl2br(@htmlspecialchars($agent_reply, \ENT_QUOTES, 'UTF-8'));
			}
		}

		if ($agent_reply && !trim(str_replace('&nbsp;', '', strip_tags($agent_reply)))) {
			$agent_reply = null;
		}

		#------------------------------
		# Find person
		#------------------------------

		$person_processor = new PersonFromEmailProcessor();
		$person_email_item = $user_reader->getFromAddress();

		$user = $person_processor->findPerson($person_email_item);
		if ($user) {
			$person_processor->passPerson($person_email_item, $user);
		} else {
			$user = $person_processor->createPerson($person_email_item, true);
		}

		#------------------------------
		# Create the ticket
		#------------------------------

		$ticket = new Ticket();
		$ticket->subject       = $user_reader->getSubject()->getSubjectUtf8();
		$ticket->person        = $user;
		$ticket->status        = 'awaiting_agent';
		$ticket->email_account = $this->account;
		$ticket->creation_system = 'gateway.agent';

		$ticket_message = new TicketMessage();
		$ticket_message->person = $user;

		if ($user_reader->getBodyHtml() && $user_reader->getBodyHtml()->body_utf8) {
			$body = $user_reader->getBodyHtml()->body_utf8;

			$body = $this->cleaner->clean($body, 'html_email_preclean');
			$body = $this->cleaner->clean($body, 'html_email_basicclean');
			$body = $this->cleaner->clean($body, 'html_email');
			$body = $this->trimHtmlWhitespace($body);
			$body = $this->cleaner->clean($body, 'html_email_postclean');


		} else {
			$body = nl2br(@htmlspecialchars(trim($user_reader->getBodyText()->body_utf8), \ENT_QUOTES, 'UTF-8'));
		}
		$ticket_message->setMessageText($body);

		if ($this->reader->getProperty('email_source')) {
			$ticket_message->email_source = $this->reader->getProperty('email_source');
		}

		$ticket->addMessage($ticket_message);

		// Add agent reply if there was one
		$agent_ticket_message = null;
		if ($agent_reply) {
			//TODO
			//$ticket->getTicketLogger()->recordExtra('is_fwd_reply', true);

			$this->logMessage('[TicketGatewayProcessor] Adding agent reply');
			$agent_reply = nl2br(htmlspecialchars($agent_reply, \ENT_QUOTES, 'UTF-8'));

			$agent_ticket_message = new TicketMessage();
			$agent_ticket_message->person = $this->person;
			$agent_ticket_message->setMessageHtml($agent_reply);
			$ticket->addMessage($this->person_message);
			$ticket->setStatus('awaiting_user');
		}

		foreach ($this->processBlobs() as $blob) {
			$attach = new TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			if (isset($this->inline_blobs[$blob->id])) {
				$attach->is_inline = true;
			}

			if ($agent_ticket_message) {
				$agent_ticket_message->addAttachment($attach);
			} else {
				$ticket_message->addAttachment($attach);
			}

			$blob->is_temp = false;
			App::getOrm()->persist($blob);
		}

		//TODO
		$tracker_extras = array(
			'fwd_via_agent' => $this->person
		);
		if ($this->person->getPref("agent_notify_override.forward.email")) {
			$tracker_extras['force_notify_email'] = array($this->person->id);
		}
		if ($this->person->getPref("agent_notify_override.forward.alert")) {
			$tracker_extras['force_notify_alert'] = array($this->person->id);
		}

		#------------------------------
		# Process new ticket
		#------------------------------

		App::getDb()->beginTransaction();

		try {
			$this->getTicketManager()->saveTicket($ticket, $executor_context);
			$this->logMessage('[TicketGatewayProcessor] Created ticket ' . $ticket['id']);

			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

		return $ticket;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function cleanBodyText($text)
	{
		if ($this->reader->isOutlookMailer()) {
			$text = Strings::standardEol($text);
			$text = str_replace("\n\n", "\n", $text);
		}

		return $text;
	}
}