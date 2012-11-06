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
 * @subpackage UserBundle
 */

namespace Application\DeskPRO\Tickets\NewTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;

/**
 * New ticket acts as the processor and domain object for a newticket form
 *
 * NOTE: New users are always created with 'validating' email addresses. If validation is disabled
 * then the ticket trigger will automatically convert the validating address into a real address.
 */
class NewTicket implements \Application\DeskPRO\People\PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\PersonProps
	 */
	public $person;

	/**
	 * The person who is running this (ex an agent?)
	 */
	protected $person_context;

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\TicketProps
	 */
	public $ticket;

	public $custom_ticket_fields = array();

	public $new_message;

	public $creation_system;
	public $creation_system_option;

	public $require_login = false;

	public $attach_blobs = array();
	public $blobs_inline_ids = array();

	protected $mode = 'untrusted';

	public $gateway;
	public $gateway_address;
	public $sent_to;

	/**
	 * @var
	 */
	protected $email_reader;

	public $logger;
	public $do_dupe_check = true;

	public function __construct($creation_system, Entity\Person $person = null)
	{
		if ($person AND !$person['id']) {
			$person = null;
		}

		$this->person = new PersonProps($person);
		$this->ticket = new TicketProps();

		$this->creation_system = $creation_system;
	}

	/**
	 * @param $email_reader
	 */
	public function setEmailReader($email_reader)
	{
		$this->email_reader = $email_reader;
	}

	public function setPersonContext(Entity\Person $person)
	{
		$this->person_context = $person;
		if ($person->is_agent) {
			$this->mode = 'trusted';
		}
	}

	public function save(array $set_extra = array())
	{
		// If we got all the way here without a subject
		// (means an email where no validation), then give a default
		if (!$this->ticket->subject) {
			$this->ticket->subject = App::getTranslator()->getPhraseText('user.tickets.no_subject');
		}

		App::getOrm()->beginTransaction();

		try {

			#------------------------------
			# Handle the person first
			#------------------------------

			$validating = null;

			$person = null;
			$email = null;
			$email_validating = null;

			if ($this->person_context->isGuest()) {

				$email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($this->person->email);

				// Email already exists on an account
				// Means use the same person, but depending on the setting we
				// might require the user to log in (in which case the ticket is a temp ticket for a bit)
				if ($email) {
					if (App::getSetting('core.existing_account_login')) {
						$person = $email->person;
						$person->name = $this->person->name;
						$this->require_login = true;

					} else {
						$person = $email->person;
						$person->name = $this->person->name;
					}

				// If we get here, then its a new user. We add the email address
				// as an email address that requires validation. If validation is disabled,
				// NewticketAction toggles it off
				} else {
					$person = Entity\Person::newContactPerson();
					$person->name = $this->person->name;
					$person->getChangeTracker()->recordExtra('email_validating', $this->person->email);
					$person->is_confirmed = false;

					if (App::getSetting('core.user_mode') == 'require_reg_agent_validation') {
						$person->is_agent_confirmed = false;
					}

					$email = new \Application\DeskPRO\Entity\PersonEmail();
					$email->setEmail($this->person->email);
					$email->person = $person;
					$email->setIsValidated(false);
					$person->addEmailAddress($email);

					App::getOrm()->persist($person);
					App::getOrm()->persist($email);
				}

			// Logged in user
			} else {
				$person = $this->person_context;

				$email = $person->findEmailAddress($this->person->email);

				// A new email address.
				// We know its unique since it passed the validator run before this
				// New addresses always require validation
				if (!$email) {
					if ($this->person->email) {
						// Existing validating address already
						$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person->email);

						// Its only valid if its ont he same person
						if (!$email_validating || !$email_validating->person || $email_validating->person->getId() != $person->getId()) {
							$email_validating = new Entity\PersonEmailValidating();
							$email_validating->email = $this->person->email;
							$email_validating->person = $person;
							App::getOrm()->persist($email_validating);
						}
					} else {
						$email = $person->primary_email;
					}
				}

				if ($this->person->name) {
					$person->name = $this->person->name;
					App::getOrm()->persist($person);
				}
			}

			App::getOrm()->flush();

			#------------------------------
			# Now ticket
			#------------------------------

			$ticket = new Entity\Ticket();
			if ($this->logger) {
				$ticket->getTicketLogger()->setLogger($this->logger);
			}
			if ($this->email_reader) {
				$ticket->email_reader = $this->email_reader;
			}
			$ticket['creation_system']  = $this->creation_system;

			if ($this->creation_system_option) {
				$ticket['creation_system_option'] = $this->creation_system_option;
			}

			$ticket['person']  = $person;
			if ($email) {
				$ticket->person_email = $email;
			}
			$ticket['subject'] = $this->ticket->subject;
			$ticket['validating'] = $validating;
			$ticket['language'] = App::getSession()->getLanguage();
			$ticket['notify_email'] = $this->ticket->notify_email;

			if ($this->sent_to) {
				$ticket['sent_to_address'] = $this->sent_to;
			}

			if ($this->gateway) {
				$ticket->email_gateway = $this->gateway;
			}
			if ($this->gateway_address) {
				$ticket->email_gateway_address = $this->gateway_address;
			}

			if ($email_validating) {
				$ticket->person_email_validating = $email_validating;
			} else {
				$ticket['person_email'] = $email;
			}

			if ($this->require_login) {
				$ticket->setStatus('hidden.temp');
			} elseif ($email_validating) {
				$ticket->setStatus('hidden.validating');
			} elseif (!$person->is_confirmed || !$person->is_agent_confirmed) {
				$ticket['status'] = 'hidden.validating';
			} else {
				$ticket['status'] = 'awaiting_agent';
			}

			foreach (array('department_id', 'category_id', 'product_id', 'priority_id') as $prop) {
				$ticket[$prop] = $this->ticket->$prop;
			}

			if (!$ticket->department) {
				$ticket->department = App::getDataService('Department')->getDefaultTicketDepartment();
			}

			$ticket_message = new Entity\TicketMessage();
			$ticket_message['creation_system'] = $this->creation_system;

			if (strpos($this->creation_system, 'web.') === 0) {
				$ticket_message->ip_address = App::getRequest()->getClientIp();
				$ticket_message->visitor = App::getSession()->getVisitor();
			}

			$ticket_message['person']  = $person;
			$ticket_message['ticket']  = $ticket;
			if ($this->ticket->message_is_html) {
				$ticket_message->setMessageHtml($this->ticket->message);
			} else {
				$ticket_message->setMessageText($this->ticket->message);
			}
			if (!$ticket_message['message']) {
				$ticket_message['message'] = '(no message)';
			}

			if ($this->ticket->message_raw) {
				$ticket_message['message_raw'] = $this->ticket->message_raw;
			}

			$attach = null;
			if ($this->ticket->new_upload) {
				$desc = App::getApi('filestorage')->createRandomPath();

				$desc->write(file_get_contents($this->ticket->new_upload->getRealPath()), array(
					'content_type' => $this->ticket->new_upload->getClientMimeType(),
					'filename' => $this->ticket->new_upload->getClientOriginalName()
				));

				$blob_id = $desc->getPath();
				$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

				$attach = new \Application\DeskPRO\Entity\TicketAttachment();
				$attach['blob'] = $blob;
				$attach['person'] = $person;

				$ticket_message->addAttachment($attach);
			}

			// Existing (pre-uploaded temp) attachments
			if ($this->ticket->attach_ids) {
				foreach ($this->ticket->attach_ids as $blob_id) {
					$blob = null;
					if ($this->ticket->attach_ids_authed) {
						if (strpos($blob_id, '-')) {
							list($blob_id, $blob_auth) = explode('-', $blob_id, 2);
							$blob = App::findEntity('DeskPRO:Blob', $blob_id);
							if ($blob && $blob->authcode != $blob_auth) {
								$blob = false;
							}
						}
					} else {
						$blob = App::findEntity('DeskPRO:Blob', $blob_id);
					}
					if ($blob) {
						$attach = new \Application\DeskPRO\Entity\TicketAttachment();
						$attach['blob'] = $blob;
						$attach['person'] = $person;

						$ticket_message->addAttachment($attach);

						$blob->is_temp = false;
						App::getOrm()->persist($blob);
					}
				}
			}

			if ($this->attach_blobs) {
				foreach ($this->attach_blobs as $blob) {
					$attach = new \Application\DeskPRO\Entity\TicketAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $person;

					if (in_array($blob->getId(), $this->blobs_inline_ids)) {
						$attach->is_inline = true;
					}

					$ticket_message->addAttachment($attach);

					$blob->is_temp = false;
					App::getOrm()->persist($blob);
				}
			}

			$this->new_message = $ticket_message;
			$ticket->addMessage($ticket_message);

			foreach ($set_extra as $k => $v) {
				$ticket[$k] = $v;
			}

			$ticket->recomputeHash();

			if ($this->do_dupe_check) {
				if ($dupe_ticket = App::getOrm()->getRepository('DeskPRO:Ticket')->checkDupeTicket($ticket)) {
					$e = new \Application\DeskPRO\Tickets\DuplicateTicketException();
					$e->ticket_id = $dupe_ticket->id;
					throw $e;
				}
			}

			App::getOrm()->persist($ticket);

			$field_manager = App::getSystemService('ticket_fields_manager');
			$post_custom_fields = isset($_POST['newticket']['custom_ticket_fields']) ? $_POST['newticket']['custom_ticket_fields'] : array();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $ticket);
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();

			if ($this->ticket->cc_emails) {
				$ccs = explode(',', $this->ticket->cc_emails);

				foreach ($ccs as &$_) {
					$_ = trim(strtolower($_));
					if (!\Orb\Validator\StringEmail::isValueValid($_)) {
						$_ = null;
					}
				}

				$ccs = array_unique($ccs);
				$ccs = \Orb\Util\Arrays::removeFalsey($ccs);

				if ($ccs) {
					foreach ($ccs as $cc) {
						$this->handleCc($ticket, $cc);
					}
					App::getOrm()->flush();
				}
			}

			App::getOrm()->commit();

		} catch (\Application\DeskPRO\Tickets\DuplicateTicketException $e) {
			App::getDb()->rollback();
			$ticket = App::getOrm()->find('DeskPRO:Ticket', $e->ticket_id);
			return $ticket;
		} catch (\Exception $e) {
			App::getOrm()->rollback();
			throw $e;
		}

		return $ticket;
	}

	public function handleCc(Entity\Ticket $ticket, $cc_email)
	{
		$gateway_address_matcher = App::getSystemService('gateway_address_matcher');

		if (!\Orb\Validator\StringEmail::isValueValid($cc_email)) {
			return null;
		}

		$addr = $gateway_address_matcher->getMatchingAddress($cc_email);
		if ($addr) {
			return null;
		}

		$person_processor = new PersonFromEmailProcessor();

		$cc = new \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress();
		$cc->email = $cc_email;
		$cc->name = '';
		$cc->name_utf8 = '';

		$cc_person = $person_processor->findPerson($cc);
		if (!$cc_person) {
			// Closed helpdesk and an unknown CC means we drop it
			if (App::getContainer()->getSetting('core.user_mode') == 'closed') {
				return null;
			}

			$cc_person = Entity\Person::newContactPerson();
			App::getOrm()->persist($cc_person);

			$cc_person_email = new \Application\DeskPRO\Entity\PersonEmail();
			$cc_person_email->setEmail($cc_email);
			$cc_person_email->person = $cc_person;
			App::getOrm()->persist($cc_person_email);

			$cc_person->addEmailAddress($cc_person_email);
			App::getOrm()->persist($cc_person);
		}

		if (!$cc_person) {
			return null;
		}

		if (!$ticket->hasParticipantPerson($cc_person)) {
			$part = $ticket->addParticipantPerson($cc_person);
			if ($part) {
				App::getOrm()->persist($part);
			}
		}

		return $cc_person;
	}
}
