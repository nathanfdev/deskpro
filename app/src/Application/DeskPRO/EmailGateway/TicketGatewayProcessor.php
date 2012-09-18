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
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EmailGateway\AbstractGatewayProcessor;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Ticket\CodeTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\ToEmailTicketDetector;
use Application\DeskPRO\EmailGateway\Ticket\InReplyToDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectMatchDetector;
use Application\DeskPRO\EmailGateway\Ticket\SubjectRefMatchDetector;
use Application\DeskPRO\EmailGateway\Ticket\Dp3Detector;
use Application\DeskPRO\EmailGateway\Cutter\cutterDefFactory;
use Application\DeskPRO\EmailGateway\Cutter\ForwardCutter;

class TicketGatewayProcessor extends AbstractGatewayProcessor
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

	/**
	 * True when there was an error converting an incoming charset to utf8.
	 * When this happens, the standard is to use the original string (unconverted)
	 * and save an original version of the message.
	 *
	 * @var bool
	 */
	protected $charset_error = false;

	/**
	 * If in reply mode, this is the ticket being replied to
	 *
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	protected $error;
	protected $source_info;
	protected $is_dp3_reply = false;
	protected $is_bounce = false;
	protected $inline_blobs = array();
	protected $dupe_inline_blobs = array();

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

		$bounce_detector = new \Application\DeskPRO\EmailGateway\Ticket\BounceDetector($this->reader, App::getOrm());
		$bounce_detector->setLogger($this->logger);

		if ($bounce_detector->isBounced()) {
			$this->logMessage("Is bounced");
			$ticket	= $bounce_detector->getGuessedTicket();
			if ($ticket) {
				$person = $ticket->person;
				$this->is_bounce = true;
			}
		}

		if (!$ticket) {
			if (!$ticket) {
				$detector = new CodeTicketDetector();
				if ($this->logger) $detector->setLogger($this->logger);
				$ticket = $detector->findExistingTicket($this->reader);

				$this->logMessage('[TicketGatewayProcessor] CodeTicketDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
			}

			// If we imported form DP3, run the old codes
			if (!$ticket && App::getSetting('core.deskpro3importer')) {
				$detector = new Dp3Detector();
				$ticket = $detector->findExistingTicket($this->reader);
				$this->logMessage('[TicketGatewayProcessor] Dp3Detector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));

				if ($ticket) {
					$this->is_dp3_reply = true;
				}
			}

			if (!$ticket) {
				// Try ref match
				$detector = new SubjectRefMatchDetector();
				$ticket = $detector->findExistingTicket($this->reader);

				$this->logMessage('[TicketGatewayProcessor] SubjectRefMatchDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
			}

			if (!$ticket) {
				// Finally try subject string match
				$detector = new SubjectMatchDetector();
				if ($this->logger) $detector->setLogger($this->logger);
				$ticket = $detector->findExistingTicket($this->reader);

				$this->logMessage('[TicketGatewayProcessor] SubjectMatchDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
			}

			if ($ticket) {
				$person = $detector->findExistingPerson($ticket, $this->reader);
				$this->logMessage('[TicketGatewayProcessor] findExistingPerson detected: ' . ($person ? $person['id'] : 'nothing'));
			}
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
				$this->logMessage('[TicketGatewayProcessor] Detected person is not on the ticket. Message will be considered a new ticket.');
				$person = null;
			}
		}

		if ($ticket AND !$person AND $detector->canAddUnknownPerson()) {

			$this->logMessage('[TicketGatewayProcessor] Could not find user, creating new');

			// If the detector didnt find a person, doesnt mean they dont exist
			$person = App::getOrm()->getRepository('DeskPRO:Person')->findOneByEmail($this->reader->getFromAddress()->getEmail());

			// But we'll create them now if they dont
			if (!$person) {
				$person = Entity\Person::newContactPerson(array('email' => $this->reader->getFromAddress()->getEmail()));
			}

			App::getDb()->beginTransaction();
			try {
				App::getOrm()->persist($person);
				App::getOrm()->flush($person);
				App::getDb()->commit();
			} catch (\Exception $e) {
				App::getDb()->rollback();
				throw $e;
			}

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

			if ($this->logger) {
				$ticket->getTicketLogger()->setLogger($this->logger);
			}

			$person_processor->passPerson($this->reader->getFromAddress(), $person);

			App::setCurrentPerson($person);

			if ($person['is_agent'] && strpos($this->reader->getBodyHtml()->getBodyUtf8(), 'DP_USER_EMAIL') === false) {
				$this->logMessage('[TicketGatewayProcessor] runNewAgentReply');
				$ret = $this->runNewAgentReply($ticket, $person);
			} else {
				$this->logMessage('[TicketGatewayProcessor] runNewUserReply');
				$ret = $this->runNewUserReply($ticket, $person);
			}
		} else {
			$this->logMessage('[TicketGatewayProcessor] Creating new ticket');
			$person = $person_processor->findPerson($this->reader->getFromAddress());
			if ($person) {
				$this->logMessage('[TicketGatewayProcessor] Found existing person: ' . $person['id']);
				$person_processor->passPerson($this->reader->getFromAddress(), $person);
			} else {
				$this->logMessage('[TicketGatewayProcessor] Creating new contact');
				if (App::getSetting('core.user_mode') == 'closed') {
					$this->logMessage('[TicketGatewayProcessor] No user and closed registration');
					$this->error = \Application\DeskPRO\Entity\EmailSource::ERR_PERM_INSUFFICIENT;

					if (!$this->is_bounce && !$this->reader->isFromRobot()) {
						$message = App::getMailer()->createMessage();
						$message->setTemplate('DeskPRO:emails_user:new-ticket-reg-closed.html.twig', array(
							'subject' => $this->reader->getSubject()->getSubjectUtf8(),
							'name' => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
						));
						$message->setTo($this->reader->getFromAddress()->getEmail());
						App::getMailer()->send($message);
					}
				}
				$person = $person_processor->createPerson($this->reader->getFromAddress());
				$this->logMessage('[TicketGatewayProcessor] Created new contact: ' . $person['id']);
			}

			App::setCurrentPerson($person);

			if ($person['is_agent'] AND ForwardCutter::subjectIsForward($this->reader->getSubject()->subject)) {
				$this->logMessage('[TicketGatewayProcessor] runNewForwardedTicket');
				$ret = $this->runNewForwardedTicket($person);
			} else {
				$this->logMessage('[TicketGatewayProcessor] runNewTicket');
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

	protected function doNewReply(Entity\Ticket $ticket, $person, $context)
	{
		$this->ticket = $ticket;

		$this->logMessage("doNewRelpy context $context");
		$this->processBlobs();

		if ($context == 'user') {
			$ticket->getTicketLogger()->recordExtra('is_user_reply', true);
		} else {
			$ticket->getTicketLogger()->recordExtra('is_agent_reply', true);
		}

		$email_info = array();

		$email_info['subject'] = $this->reader->getSubject()->getSubjectUtf8();
		if (!$email_info['subject'] && $this->reader->getSubject()->getSubject()) {
			$email_info['subject'] = $this->reader->getSubject()->getSubject();
		}

		if ($this->is_dp3_reply) {
			$email_info = array_merge($email_info, $this->getEmailBodyInfoDp3());
		} else {
			$email_info = array_merge($email_info, $this->getEmailBodyInfo());
		}

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'email_info' => $email_info,
			'cancel' => false,
		));
		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_NEWREPLY, $ev);

		if ($ev->cancel) {
			$this->logMessage('[TicketGatewayProcessor] doNewReply cancel');
			return null;
		}

		if ($context == 'agent' && !$email_info['found_top_marker']) {
			// The marker is required for agent emails
			$this->logMessage('doNewRelpy agent reply missing marker');
			$this->error = \Application\DeskPRO\Entity\EmailSource::ERR_MISSING_MARKER;

			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_agent:error-marker-missing.html.twig', array(
				'ticket'  => $ticket,
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			App::getMailer()->send($message);

			return null;
		}

		$email_info = $ev->email_info;

		if ($this->is_bounce) {
			$ticket->getTicketLogger()->recordExtra('is_bounce_message', true);
		}
		$message = new Entity\TicketMessage();
		$message->email_reader = $this->reader;
		if ($this->reader->hasProperty('email_source')) {
			$message['email_source'] = $this->reader->getProperty('email_source');
		}

		if ($person->is_agent) {
			$message->creation_system = 'gateway.agent';
		} else {
			$message->creation_system = 'gateway.person';
		}

		$message['ticket'] = $ticket;
		$message['person'] = $person;
		$message['email'] = $this->reader->getFromAddress()->getEmail();

		$message['message'] = $email_info['body'];
		$message['message_full'] = $email_info['body_full'];

		$message['show_full_hint'] = false;
		$inline_reply_detector = new \Application\DeskPRO\EmailGateway\TicketGateway\DetectInlineReply(App::getOrm(), $this->reader);
		if ($this->logger) {
			$inline_reply_detector->setLogger($this->logger);
		}

		if ($inline_reply_detector->hasDifferentMessage()) {
			$message['show_full_hint'] = true;
		}

		foreach ($this->processBlobs() as $blob) {

			if (isset($this->dupe_inline_blobs[$blob->getId()])) {
				continue;
			}

			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $person;

			if (isset($this->inline_blobs[$blob->getId()])) {
				$attach->is_inline = true;
			}

			$message->addAttachment($attach);
		}

		if ($dupe_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($message, $ticket)) {
			$this->error = \Application\DeskPRO\Entity\EmailSource::ERR_DUPE;
			$this->logMessage('[TicketGatewayProcessor] doNewReply duplicate message ' . $dupe_message->getId());
			return $dupe_message;
		}

		$ticket->addMessage($message);

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}

		if (!$this->is_bounce) {
			if ($person['is_agent'] && $context == 'agent') {
				$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_user');
				$ticket['status'] = Entity\Ticket::STATUS_AWAITING_USER;
			} else {
				$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_agent');
				$ticket['status'] = Entity\Ticket::STATUS_AWAITING_AGENT;
			}
		}

		$charset_error = $this->charset_error;
		App::getDb()->beginTransaction();

		try {
			App::getOrm()->persist($ticket);
			App::getOrm()->persist($person);
			App::getOrm()->persist($message);
			App::getOrm()->flush();

			if ($charset_error) {
				App::getOrm()->getConnection()->insert('tickets_messages_raw', array(
					'message_id' => $message['id'],
					'raw'        => $email_info['body'],
					'charset'    => $charset_error,
				));
			}
			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

		$ev = $this->createGatewayEvent(array(
			'ticket' => $ticket,
			'person' => $person,
			'message' => $message
		));
		$this->event_dispatcher->dispatch(self::EVENT_NEWREPLY, $ev);

		return $message;
	}

	protected function getEmailBodyInfo()
	{
		$email_info = array();

		$inline_images = new InlineImageTokens($this->reader);
		$inline_images2 = new InlineImageTokens($this->reader);

		if ($this->reader->getBodyHtml()->getBody()) {
			$this->logMessage('[TicketGatewayProcessor] doNewReply read HTML email');
			$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
			if (!$email_info['body']) {
				$email_info['body'] = $this->reader->getBodyHtml()->getBody();
				$this->charset_error = $this->reader->getBodyHtml()->getOriginalCharset();
			}
			$email_info['body_is_html'] = true;

			// If the document is too complex then htmlpurifier can crash, lets use the plaintext version instead
			if (substr_count($email_info['body'], '>') > 15000) {
				$this->logMessage('[TicketGatewayProcessor] Document too long, using plaintext');
				$email_info['body'] = $this->reader->getBodyText()->getBodyUtf8();
				if ($email_info['body']) {
					$email_info['body'] = str_replace(array("\n", "\r"), '', nl2br(htmlspecialchars($email_info['body'], \ENT_QUOTES, 'UTF-8')));
				} else {
					$email_info['body'] = strip_tags($this->reader->getBodyHtml()->getBodyUtf8());
					$email_info['body'] = str_replace(array("\n", "\r"), '', nl2br(htmlspecialchars($email_info['body'], \ENT_QUOTES, 'UTF-8')));
				}
				$email_info['body_is_html'] = false;
			}

		} else {
			$this->logMessage('[TicketGatewayProcessor] doNewReply read text email');
			$txt = $this->reader->getBodyText()->getBodyUtf8();
			if (!$txt && $this->reader->getBodyText()->getBody()) {
				$txt = $this->reader->getBodyText()->getBody();
				$this->charset_error = $this->reader->getBodyText()->getOriginalCharset();
			}

			$email_info['body'] = str_replace(array("\n", "\r"), '', nl2br(htmlspecialchars($txt, \ENT_QUOTES, 'UTF-8')));
			$email_info['body_is_html'] = false;
		}

		$has_cut = false;

		$email_info['body_raw'] = $email_info['body'];
		$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email_preclean');
		$email_info['body_full'] = $email_info['body'];

		// Always generic cut from the DP_TOP_MARK position first
		// The PatternCutter will trim off the remaining quoted headers
		$cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
		$generic_cut = $cut->cutQuoteBlock($email_info['body'], $email_info['body_is_html']);
		if ($email_info['body'] != $generic_cut) {
			$email_info['body'] = $generic_cut;
			$email_info['found_top_marker'] = true;
			$has_cut = true;
		} else {
			$email_info['found_top_marker'] = false;
		}

		if ($email_info['body_is_html']) {
			$cutter = new \Application\DeskPRO\EmailGateway\Cutter\PatternCutter();
			$pattern_config = new \Application\DeskPRO\Config\UserFileConfig('html-cut-patterns');
			$cutter->addPatterns($pattern_config->all());

			$email_info['body'] = $cutter->cutQuoteBlock($email_info['body'], true);

			if ($cutter->getMatchedPatterns()) {
				$has_cut = true;
				foreach ($cutter->getMatchedPatterns() as $p) {
					$this->logMessage("Cutter matched pattern: " . $p->getPattern());
				}
			} else {
				$this->logMessage("Cutter did not match any pattern");
			}
		}

		if (!$has_cut) {
			$email_info['body_full'] = '';
		}

		// Cut down the quoted message part to 10000 chars
		$cut_len = strlen($email_info['body']);
		$full_len = strlen($email_info['body_full']);

		if (($full_len - $cut_len) > 8000) {
			$email_info['body_full'] = substr($email_info['body_full'], 0, 8000 + $cut_len);

			// Simple way to try and handle if we cut in the middle of a tag name
			$tag_start_pos = strrpos($email_info['body_full'], '<');
			if ($tag_start_pos) {
				$tag_end_pos = strrpos($email_info['body_full'], '>');
				if ($tag_end_pos === false || $tag_end_pos < $tag_start_pos) {
					$email_info['body_full'] = substr($email_info['body_full'], 0, $tag_start_pos);
				}
			}
		}

		if ($email_info['body_is_html']) {
			// Replace inline image tags with tokens
			$email_info['body'] = $inline_images->processTokens($email_info['body']);
			$email_info['body_full'] = $inline_images2->processTokens($email_info['body_full']);

			// The basic cleaner cleans out outlook type stuff like empty <p>'s that cause whitespace
			$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email_basicclean');
			$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email');

			$email_info['body_full'] = $this->cleaner->clean($email_info['body_full'], 'html_email_basicclean');
			$email_info['body_full'] = $this->cleaner->clean($email_info['body_full'], 'html_email');
		}

		if ($email_info['body_is_html']) {
			$email_info['body'] = $this->trimHtmlWhitespace($email_info['body']);
			$email_info['body_full'] = $this->trimHtmlWhitespace($email_info['body_full']);
		}

		$email_info['body'] = $this->replaceInlineAttachTokens($email_info['body'], $inline_images);
		$email_info['body_full'] = $this->replaceInlineAttachTokens($email_info['body_full'], $inline_images2);

		return $email_info;
	}

	protected function getEmailBodyInfoDp3()
	{
		$email_info = array();
		$email_info['body_is_html'] = false;

		$inline_images = new InlineImageTokens($this->reader);

		$this->logMessage('[TicketGatewayProcessor] Processing DP3 reply text');

		if ($this->reader->getBodyText()->getBodyUtf8()) {
			$this->logMessage('[TicketGatewayProcessor] doNewReply read text email');
			$txt = $this->reader->getBodyText()->getBodyUtf8();
			if (!$txt && $this->reader->getBodyText()->getBody()) {
				$txt = $this->reader->getBodyText()->getBody();
				$this->charset_error = $this->reader->getBodyText()->getOriginalCharset();
			}

			$email_info['body'] = $txt;
		} else {
			$this->logMessage('[TicketGatewayProcessor] doNewReply read HTML email');
			$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
			if (!$email_info['body']) {
				$email_info['body'] = strip_tags($this->reader->getBodyHtml()->getBody());
				$this->charset_error = $this->reader->getBodyHtml()->getOriginalCharset();
			}

			// Replace inline image tags with tokens
			$email_info['body'] = $inline_images->processTokens($email_info['body']);
			$email_info['body_full'] = $inline_images->processTokens($email_info['body']);
		}

		$email_info['body_raw'] = $email_info['body'];
		$email_info['body_full'] = $email_info['body'];

		$agent_pos_1 = strpos($email_info['body'], '=== Enter your reply below this line ===');
		$agent_pos_2 = strpos($email_info['body'], '=== Enter your reply above this line ===');

		#------------------------------
		# Agent markers
		#------------------------------

		if ($agent_pos_1 !== false && $agent_pos_2 !== false) {
			$email_info['body'] = \Orb\Util\Strings::getBetweenBoundary(
				$email_info['body'],
				'=== Enter your reply below this line ===',
				'=== Enter your reply above this line ==='
			);

		#------------------------------
		# User email
		#------------------------------

		} else {
			$user_pos_1 = strpos($email_info['body'], '========= Please enter your reply ABOVE this line =========');
			if ($user_pos_1 !== false) {
				$email_info['body'] = \Orb\Util\Strings::getAboveBoundary(
					$email_info['body'],
					'========= Please enter your reply ABOVE this line ========='
				);
			}
		}

		$cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
		$email_info['body'] = $cut->cutQuoteBlock($email_info['body'], $email_info['body_is_html']);

		$email_info['body'] = trim($email_info['body'], " >\n\r");

		$email_info['body'] = nl2br(htmlspecialchars($email_info['body'], \ENT_QUOTES, 'UTF-8'));
		$email_info['body_full'] = nl2br(htmlspecialchars($email_info['body_full'], \ENT_QUOTES, 'UTF-8'));

		$email_info['body'] = $this->replaceInlineAttachTokens($email_info['body'], $inline_images);

		return $email_info;
	}

	public function trimHtmlWhitespace($html)
	{
		return \Orb\Util\Strings::trimHtmlAdvanced($html);
	}

	public function handleCc($ticket, array $ccs)
	{
		$gateway_address_matcher = new \Application\DeskPRO\EmailGateway\AddressMatcher(App::getContainer()->getEm());

		$count = 0;
		foreach ($ccs as $cc) {

			// Max 10 CC's to prevent mass spamming
			if ($count >= 10) {
				break;
			}

			$cc_email = $cc->getEmail();
			$addr = $gateway_address_matcher->getMatchingAddress($cc_email);
			if ($addr) {
				$this->logMessage("Skipping cc: $cc_email (matches gateway address {$addr->id})");
				continue;
			}

			$person_processor = new PersonFromEmailProcessor();

			$cc_person = $person_processor->findPerson($cc);
			if (!$cc_person) {
				// Closed helpdesk and an unknown CC means we drop it
				if (App::getContainer()->getSetting('core.user_mode') == 'closed') {
					$this->logMessage("Skipping cc: $cc_email (no person match and closed helpdesk)");
					continue;
				}
				$cc_person = $person_processor->createPerson($cc, true);
				$this->logMessage("Added cc: $cc_email (Person {$cc_person->id})");
			}

			if (!$cc_person) {
				continue;
			}

			if (!$ticket->hasParticipantPerson($cc_person)) {
				$ticket->addParticipantPerson($cc_person);
				$count++;
			}
		}
	}


	############################################################################
	# New Reply: Agent
	############################################################################

	protected function runNewAgentReply(Entity\Ticket $ticket, Entity\Person $person)
	{
		$message = $this->doNewReply($ticket, $person, 'agent');
		return $message;
	}


	############################################################################
	# New Reply: User
	############################################################################

	protected function runNewUserReply(Entity\Ticket $ticket, Entity\Person $person)
	{
		$message = $this->doNewReply($ticket, $person, 'user');
		return $message;
	}


	############################################################################
	# New Ticket
	############################################################################

	protected function runNewTicket(Entity\Person $person)
	{
		#------------------------------
		# Read email body/subject
		#------------------------------

		$email_info = array();

		$this->processBlobs();
		$inline_images = new InlineImageTokens($this->reader);

		$email_info['subject'] = $this->reader->getSubject()->getSubjectUtf8();
		if (!$email_info['subject'] && $this->reader->getSubject()->getSubject()) {
			$email_info['subject'] = $this->reader->getSubject()->getSubject();
		}

		if ($this->reader->getBodyHtml()->getBody()) {
			$this->logMessage('[TicketGatewayProcessor] runNewTicket read HTML email');
			$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
			if (!$email_info['body']) {
				$email_info['body'] = $this->reader->getBodyHtml()->getBody();
				$this->charset_error = $this->reader->getBodyHtml()->getOriginalCharset();
			}
			$email_info['body_is_html'] = true;
		} else {
			$this->logMessage('[TicketGatewayProcessor] runNewTicket read text email');
			$txt = $this->reader->getBodyText()->getBodyUtf8();
			if (!$txt && $this->reader->getBodyText()->getBody()) {
				$txt = $this->reader->getBodyText()->getBody();
				$this->charset_error = $this->reader->getBodyText()->getOriginalCharset();
			}

			$email_info['body'] = str_replace(array("\n", "\r"), '', nl2br(htmlspecialchars($txt, \ENT_QUOTES, 'UTF-8')));
			$email_info['body_is_html'] = false;
		}

		// Replace inline image tags with tokens
		$email_info['body_raw'] = $email_info['body'];
		$email_info['body'] = $inline_images->processTokens($email_info['body']);
		$email_info['body_full'] = '';

		if ($email_info['body_is_html']) {
			// The basic cleaner cleans out outlook type stuff like empty <p>'s that cause whitespace
			$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email_preclean');
			$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email_basicclean');
			$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email');
		}

		$email_info['body'] = \Orb\Util\Strings::trimHtml($email_info['body']);

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

		$email_info['body'] = $this->replaceInlineAttachTokens($email_info['body'], $inline_images);

		#------------------------------
		# If the user is new with no lang, then try to guess based off the email
		#------------------------------

		if ($person->isNewPerson() || !$person->getRealLanguage()) {

			/** @var $lang_detect \Application\DeskPRO\Languages\Detect */
			$lang_detect = App::getSystemService('language_detect');
			$this->logMessage("Detectable languages: " . implode(', ', $lang_detect->getDetectableLanguages()));

			$lang = $lang_detect->detectLanguage($email_info['body']);
			if ($lang) {
				$this->logMessage("Detected language {$lang->title} (#{$lang->id})");
				$person->language = $lang;
			}
		}

		#------------------------------
		# Create the ticket
		#------------------------------

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_GATEWAY_PERSON,
			$person
		);
		$newticket->setPersonContext($person);
		$newticket->gateway = $this->gateway;
		$newticket->gateway_address = $this->gateway_address;

		if ($this->logger) {
			$newticket->logger = $this->logger;
		}
		$newticket->setPersonContext($person);

		$newticket->ticket->subject = $email_info['subject'];
		$newticket->ticket->message = $email_info['body'];
		$newticket->ticket->message_raw = $email_info['body_raw'];
		$newticket->ticket->message_is_html = true;
		$newticket->ticket->department_id = null;

		if ($this->gateway_address && $this->gateway_address->match_type == 'exact') {
			$this->logMessage('[TicketGatewayProcessor] Setting ticket email: ' . $this->gateway_address->match_pattern);
			$newticket->ticket->notify_email = $this->gateway_address->match_pattern;
		} else {
			$this->logMessage('[TicketGatewayProcessor] Could not find ticket email!');
		}

		#------------------------------
		# Check for dupe first
		#------------------------------

		if ($person && !$person->isNewPerson()) {
			$ticket_message = new Entity\TicketMessage();
			$ticket_message['person']  = $person;
			$ticket_message->setMessageHtml($email_info['body']);
			$ticket_message->withNewSubject = $newticket->ticket->subject;

			if ($dupe_message = App::getOrm()->getRepository('DeskPRO:TicketMessage')->checkDupeMessage($ticket_message)) {
				$this->error = \Application\DeskPRO\Entity\EmailSource::ERR_DUPE;
				$this->logMessage('[TicketGatewayProcessor] Duplicate message ' . $dupe_message->getId());
				return $dupe_message;
			}
		}

		#------------------------------
		# Process new ticket
		#------------------------------

		App::getDb()->beginTransaction();

		try {
			$newticket->attach_blobs = $this->processBlobs();

			$newticket->blobs_inline_ids = array();
			foreach ($newticket->attach_blobs as $bid => $b) {
				if (isset($this->inline_blobs[$bid])) {
					$newticket->blobs_inline_ids[] = $bid;
				}
			}

			$ticket = $newticket->save();

			$this->logMessage('[TicketGatewayProcessor] Ticket record ' . $ticket->id);

			$message = $newticket->new_message;
			$message['email'] = $this->reader->getFromAddress()->getEmail();

			if ($this->reader->getCcAddresses()) {
				$this->logMessage('[TicketGatewayProcessor] Has CC');
				$this->handleCc($ticket, $this->reader->getCcAddresses());
			}

			if ($this->reader->hasProperty('email_source')) {
				$message['email_source'] = $this->reader->getProperty('email_source');
			}

			// Set the proper email address on the ticket from the users account
			if ($this->reader->getFromAddress()->email != $person->getPrimaryEmailAddress()) {
				$email_rec = $person->findEmailAddress($this->reader->getFromAddress()->getEmail());
				if ($email_rec) {
					$ticket->person_email = $email_rec;
				}
			}

			$ticket->gateway = $this->getGateway();
			$ticket->gateway_address = $this->getGatewayAddress();

			$message['message'] = $email_info['body'];

			App::getOrm()->persist($ticket);
			App::getOrm()->persist($message);
			App::getOrm()->persist($person);
			App::getOrm()->flush();

			if ($this->charset_error) {
				App::getOrm()->getConnection()->insert('tickets_messages_raw', array(
					'message_id' => $message['id'],
					'raw'        => $email_info['body'],
					'charset'    => $this->charset_error,
				));
			}

			$this->logMessage('[TicketGatewayProcessor] Created ticket ' . $ticket['id']);

			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}

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
		$this->logMessage('[TicketGatewayProcessor] Forwarded ticket by ' . $agent->getId() . ' ' . $agent->getDisplayContact());

		#------------------------------
		# Read in email props and create cutter
		#------------------------------

		$email_info = array();
		$email_info['subject'] = $this->reader->getSubject()->subject;
		if ($this->reader->getBodyText()->getBody()) {
			$email_info['body'] = $this->reader->getBodyText()->getBodyUtf8();
			$email_info['body_is_html'] = false;
		} else {
			$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
			$email_info['body_is_html'] = false;

			if ($email_info['body_is_html'] && $this->cleaner && $this->cleaner->supportsType('html_email')) {
				$email_info['body'] = $this->cleaner->clean($email_info['body'], 'html_email');
			}

			$email_info['body'] = strip_tags($email_info['body']);
		}

		$fwd_cutter = new ForwardCutter($email_info['body'], $email_info['body_is_html'], $this->cutterDef);

		$ev = $this->createGatewayEvent(array(
			'email_info' => $email_info,
			'fwd_cutter' => $fwd_cutter,
			'cancel' => false,
		));

		$this->event_dispatcher->dispatch(self::EVENT_BEFORE_FWD_NEWTICKET, $ev);

		if ($ev->cancel OR !$fwd_cutter->isValid()) {
			$this->logMessage('[TicketGatewayProcessor] Invalid forward');
			$this->error = \Application\DeskPRO\Entity\EmailSource::ERR_INVALID_FWD;

			$message = App::getMailer()->createMessage();
			$message->setTemplate('DeskPRO:emails_agent:error-invalid-forward.html.twig', array(
				'subject' => $this->reader->getSubject()->getSubjectUtf8(),
				'name'    => $this->reader->getFromAddress()->getName() ?: $this->reader->getFromAddress()->getEmail(),
			));
			$message->setTo($this->reader->getFromAddress()->getEmail());
			App::getMailer()->send($message);

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
			Entity\Ticket::CREATED_GATEWAY_PERSON,
			$person
		);
		$newticket->setPersonContext($person);
		$newticket->gateway = $this->gateway;
		$newticket->gateway_address = $this->gateway_address;

		if ($this->logger) {
			$newticket->logger = $this->logger;
		}

		$newticket->ticket->subject = $email_info['subject'];

		$body = $fwd_cutter->getForwardedMessage();
		$newticket->ticket->message = $body;

		App::getOrm()->beginTransaction();
		$ticket = $newticket->save(array('agent' => $agent));

		if ($this->reader->hasProperty('email_source')) {
			$message = $newticket->new_message;
			$message['email'] = $fwd_cutter->getUserEmailItem()->getEmail();
			$message['email_source'] = $this->reader->getProperty('email_source');

			App::getOrm()->persist($message);
			App::getOrm()->flush();
		}

		App::getOrm()->commit();

		// Add agent reply if there was one
		$agent_reply = $fwd_cutter->getReply();
		if ($agent_reply) {

			$agent_reply = nl2br(htmlspecialchars($agent_reply, \ENT_QUOTES, 'UTF-8'));

			App::getOrm()->beginTransaction();
			$agent_message = new \Application\DeskPRO\Entity\TicketMessage();
			$agent_message->email_reader = $this->reader;
			$agent_message->person = $agent;
			$agent_message['message'] = $agent_reply;

			$ticket->setStatus('awaiting_user');
			$ticket->addMessage($agent_message);

			App::getOrm()->persist($ticket);
			App::getOrm()->flush($ticket);
			App::getOrm()->commit();
		}

		return $ticket;
	}

	public function replaceInlineAttachTokens($body, InlineImageTokens $inline_images)
	{
		$exist_inline_blobs = array();

		if ($this->ticket) {
			$blob_hashes = array();

			foreach ($this->processed_blobs as $blob) {
				$blob_hashes[] = $blob->blob_hash;
			}

			if ($blob_hashes) {
				$exist_attach = App::getOrm()->createQuery("
					SELECT a, b
					FROM DeskPRO:TicketAttachment a
					LEFT JOIN a.blob b
					WHERE a.ticket = ?0 AND b.blob_hash IN (?1)
				")->execute(array($this->ticket, $blob_hashes));

				foreach ($exist_attach as $a) {
					$exist_inline_blobs[$a->blob->blob_hash] = $a->blob;
				}
			}
		}

		foreach ($inline_images->getCids() as $cid) {
			if (!isset($this->processed_blobs_cid[$cid])) {
				continue;
			}

			$blob = $this->processed_blobs_cid[$cid];

			// If this ticket already has a blob like this,
			// then mark it as a dupe and rewrite the inline reference
			// to the one we've already saved
			if (isset($exist_inline_blobs[$blob->blob_hash])) {
				$this->logMessage(sprintf("Duplicate inline blob %s is being discarded, existing blob %s will be used", $blob->getFilenameSafe(), $blob->getId()));
				$this->dupe_inline_blobs[$blob->getId()] = $blob;
				$blob = $exist_inline_blobs[$blob->blob_hash];
			}

			if ($blob->isImage()) {
				$this->inline_blobs[$blob->getId()] = $blob;
				$replace = '[attach:image:' . $blob->getAuthId() . ':' . $blob->getFilenameSafe() . ']';
			} else {
				$replace = '[attach:file:' . $blob->getAuthId() . ':' . $blob->getFilenameSafe() . ']';
			}

			$body = $inline_images->replaceToken($cid, $replace, $body);
		}

		return $body;
	}

	public function getErrorCode()
	{
		return $this->error;
	}

	public function getSourceInfo()
	{
		if ($this->source_info) {
			$messages = is_array($this->source_info) ? $this->source_info : array($this->source_info);
		} else {
			$messages = array();
		}

		if (isset($this->options['logger_messages'])) {
			$messages = array_merge($messages, $this->options['logger_messages']->getMessages());
		}

		return $messages;
	}
}
