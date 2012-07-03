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
use Application\DeskPRO\EmailGateway\Cutter\CutterDefFactory;
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

	protected $error;
	protected $source_info;
	protected $is_dp3_reply = false;

	public function logMessage($message, $pri = 'debug')
	{
		$line = sprintf("[%s %s] %s", date('Y-m-d H:i:s'), $message, $pri);

		parent::logMessage($message, $pri);

		if (!$this->source_info) {
			$this->source_info = array();
		}

		$this->source_info[] = $line;
	}

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

		/*
		if (App::getSetting('core_tickets.gateway_catchall')) {
			$detector = new ToEmailTicketDetector(App::getSetting('core_tickets.gateway_catchall'));
			$ticket = $detector->findExistingTicket($this->reader);

			$this->logMessage('[TicketGatewayProcessor] ToEmailTicketDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
		}
		*/

		if (!$ticket) {
			$detector = new CodeTicketDetector();
			$ticket = $detector->findExistingTicket($this->reader);

			$this->logMessage('[TicketGatewayProcessor] CodeTicketDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
		}

		/*
		if (!$ticket) {
			// Try to find it from In-Reply-To
			$detector = new InReplyToDetector();
			$ticket = $detector->findExistingTicket($this->reader);

			$this->logMessage('[TicketGatewayProcessor] InReplyToDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
		}
		*/

		// If we imported form DP3, run the old codes
		if (!$ticket && App::getSetting('core.deskpro3importer')) {
			$detector = new Dp3Detector();
			$ticket = $detector->findExistingTicket($this->reader);
			$this->logMessage('[TicketGatewayProcessor] Dp3Detector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
			$this->is_dp3_reply = true;
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
			$ticket = $detector->findExistingTicket($this->reader);

			$this->logMessage('[TicketGatewayProcessor] SubjectMatchDetector detected: ' . ($ticket ? $ticket['id'] : 'nothing'));
		}

		if ($ticket) {
			$person = $detector->findExistingPerson($ticket, $this->reader);
			$this->logMessage('[TicketGatewayProcessor] findExistingPerson detected: ' . ($person ? $person['id'] : 'nothing'));
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
			$person_processor->passPerson($this->reader->getFromAddress(), $person);

			App::setCurrentPerson($person);

			if ($person['is_agent']) {
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

	protected function doNewReply($ticket, $person)
	{
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

		$email_info = $ev->email_info;

		$message = new Entity\TicketMessage();
		$message->email_reader = $this->reader;
		if ($this->reader->hasProperty('email_source')) {
			$message['email_source'] = $this->reader->getProperty('email_source');
		}

		$message['ticket'] = $ticket;
		$message['person'] = $person;
		$message['email'] = $this->reader->getFromAddress()->getEmail();

		$message['message'] = $email_info['body'];
		$message['message_full'] = $email_info['body_full'];

		$message['show_full_hint'] = false;
		$inline_reply_detector = new \Application\DeskPRO\EmailGateway\TicketGateway\DetectInlineReply(App::getOrm(), $this->reader);
		if ($inline_reply_detector->hasDifferentMessage()) {
			$message['show_full_hint'] = true;
		}

		foreach ($this->processBlobs() as $blob) {
			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $person;

			$message->addAttachment($attach);
		}

		$ticket->addMessage($message);

		if ($this->reader->getCcAddresses()) {
			$this->handleCc($ticket, $this->reader->getCcAddresses());
		}

		if ($person['is_agent']) {
			$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_user');
			$ticket['status'] = Entity\Ticket::STATUS_AWAITING_USER;
		} else {
			$this->logMessage('[TicketGatewayProcessor] doNewReply set status = awaiting_agent');
			$ticket['status'] = Entity\Ticket::STATUS_AWAITING_AGENT;
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

		if ($this->reader->getBodyHtml()->getBody()) {
			$this->logMessage('[TicketGatewayProcessor] doNewReply read HTML email');
			$email_info['body'] = $this->reader->getBodyHtml()->getBodyUtf8();
			if (!$email_info['body']) {
				$email_info['body'] = $this->reader->getBodyHtml()->getBody();
				$this->charset_error = $this->reader->getBodyHtml()->getOriginalCharset();
			}
			$email_info['body_is_html'] = true;

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

		$email_info['body_full'] = $email_info['body'];

		// Get rid of our cut line
		$email_info['body_full'] = str_replace('_______________________.', '', $email_info['body_full']);
		if ($email_info['body_is_html']) {
			$email_info['body_full'] = $this->cleaner->clean($email_info['body_full'], 'html_email');
		}

		$has_cut = false;

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
			$cut = new \Application\DeskPRO\EmailGateway\Cutter\Def\Generic();
			$email_info['body'] = $cut->cutQuoteBlock($email_info['body'], $email_info['body_is_html']);
		}

		if ($email_info['body_is_html']) {
			$email_info['body'] = $this->trimHtmlWhitespace($email_info['body']);
			$email_info['body_full'] = $this->trimHtmlWhitespace($email_info['body_full']);
		}

		return $email_info;
	}

	protected function getEmailBodyInfoDp3()
	{
		$email_info = array();
		$email_info['body_is_html'] = false;

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
		}

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

		return $email_info;
	}

	public function trimHtmlWhitespace($html)
	{
		return \Orb\Util\Strings::trimHtmlAdvanced($html);
	}

	public function handleCc($ticket, array $ccs)
	{
		App::getOrm()->beginTransaction();

		foreach ($ccs as $cc) {
			$person_processor = new PersonFromEmailProcessor();

			$cc_person = $person_processor->findPerson($cc);
			if (!$cc_person) {
				// Closed helpdesk and an unknown CC means we drop it
				if (App::getContainer()->getSetting('core.user_mode') == 'closed') {
					continue;
				}
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
		#------------------------------
		# Read email body/subject
		#------------------------------

		$email_info = array();

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

		if ($email_info['body_is_html'] && $this->cleaner && $this->cleaner->supportsType('html_email')) {
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

		#------------------------------
		# If the user is new with no lang, then try to guess based off the email
		#------------------------------

		$lang_codes = App::getDataService('Language')->getLangCodes();
		if ($person->isNewPerson() && !$person->getRealLanguage() && count($lang_codes) > 1) {
			$l = new \Text_LanguageDetect();
			$l->setNameMode(3);

			$set_lang_codes = array();
			foreach ($lang_codes as $code) {
				if ($l->languageExists($code)) {
					$set_lang_codes[] = $code;
				}
			}

			if ($set_lang_codes) {
				$l->omitLanguages($set_lang_codes, true);

				$body_test = strip_tags($email_info['body']);
				$detected_lang = $l->detectSimple($body_test);

				if ($detected_lang) {
					$lang = App::getDataService('Language')->findLangCode($detected_lang);
					$person->language = $lang;
				}
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

		$newticket->ticket->subject = $email_info['subject'];
		$newticket->ticket->message = $email_info['body'];

		$dep_id = App::getDb()->fetchColumn("SELECT id FROM departments WHERE parent_id IS NULL ORDER BY title ASC LIMIT 1");
		$newticket->ticket->department_id = $dep_id;

		if ($this->gateway_address && $this->gateway_address->match_type == 'exact') {
			$this->logMessage('[TicketGatewayProcessor] Setting ticket email: ' . $this->gateway_address->match_pattern);
			$newticket->ticket->notify_email = $this->gateway_address->match_pattern;
		} else {
			$this->logMessage('[TicketGatewayProcessor] Could not find ticket email!');
		}

		App::getOrm()->beginTransaction();

		$ticket = $newticket->save();

		$ticket->email_gateway = $this->gateway;
		$ticket->email_gateway_address = $this->gateway_address;

		$this->logMessage('[TicketGatewayProcessor] Ticket record ' . $ticket->id);

		$message = $newticket->new_message;
		$message['email'] = $this->reader->getFromAddress()->getEmail();

		foreach ($this->processBlobs() as $blob) {
			$this->logMessage('[TicketGatewayProcessor] Adding blob ' . $blob->id);
			$attach = new Entity\TicketAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $person;

			$message->addAttachment($attach);
		}

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
			$em->getConnection()->insert('tickets_messages_raw', array(
				'message_id' => $message['id'],
				'raw'        => $email_info['body'],
				'charset'    => $this->charset_error,
			));
		}

		$this->logMessage('[TicketGatewayProcessor] Created ticket ' . $ticket['id']);

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
			$person = $person_processor->createPerson($person_email_item, false);
		}

		#------------------------------
		# Create ticket
		#------------------------------

		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_GATEWAY_PERSON,
			$person
		);
		$newticket->setPersonContext($person);

		$newticket->ticket->subject = $email_info['subject'];

		$body = $fwd_cutter->getForwardedMessage();
		$body = nl2br(htmlspecialchars($body, \ENT_QUOTES, 'UTF-8'));

		$newticket->ticket->message = $body;

		App::getOrm()->beginTransaction();
		$ticket = $newticket->save();

		$ticket['agent'] = $agent;
		App::getOrm()->persist($ticket);

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

			$ticket->addMessage($agent_message);

			App::getOrm()->persist($ticket);
			App::getOrm()->commit();
		}

		return $ticket;
	}

	public function getErrorCode()
	{
		return $this->error;
	}

	public function getSourceInfo()
	{
		if (!$this->source_info) {
			return null;
		}

		if (!is_array($this->source_info)) {
			$this->source_info = array($this->source_info);
		}
		return $this->source_info;
	}
}
