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

	public $require_login = false;

	public $attach_blobs = array();

	protected $mode = 'untrusted';

	public $gateway;
	public $gateway_address;

	/**
	 * @var
	 */
	protected $email_reader;

	public $logger;

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

	public function save()
	{
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
				$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person->email);

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

					$email_validating = null;

				// Email doesnt exist,
				// Might already be validating
				} elseif ($email_validating) {
					$validating = 'new';
					$person = $email_validating->person;
					$person->getChangeTracker()->recordExtra('email_validating', $this->person->email);

				// If we get here, then its a new user. We add the email address
				// as a validation email address. The trigger NewTicketAction will turn it into
				// a real email address if validation isn't required
				} else {
					$person = Entity\Person::newContactPerson();
					$person->name = $this->person->name;
					$person->getChangeTracker()->recordExtra('email_validating', $this->person->email);

					$email_validating = new Entity\PersonEmailValidating();
					$email_validating->email = $this->person->email;
					$email_validating->person = $person;
					$person->email_validating = $email_validating;
					App::getOrm()->persist($person);
					App::getOrm()->persist($email_validating);
				}

			// Logged in user
			} else {
				$person = $this->person_context;

				if (strpos($this->creation_system, 'gateway.') !== false) {
					// From PersonFromEmailProcessor
					$email_validating = $person->email_validating;
				} else {
					// A new email address.
					// We know its unique since it passed the validator run before this
					// New addresses always require validation
					if (!$person->findEmailAddress($this->person->email)) {

						// Existing validating address already
						$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person->email);

						// Or create a new one
						if (!$email_validating) {
							$email_validating = new Entity\PersonEmailValidating();
							$email_validating->email = $this->person->email;
							$email_validating->person = $person;
							App::getOrm()->persist($email_validating);
						}
					}

					if ($this->person->name) {
						$person->name = $this->person->name;
						App::getOrm()->persist($person);
					}
				}
			}

			if ($email_validating) {
				App::getOrm()->persist($email_validating);
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
			$ticket['person']  = $person;
			$ticket['subject'] = $this->ticket->subject;
			$ticket['validating'] = $validating;
			$ticket['language'] = App::getSession()->getLanguage();
			$ticket['notify_email'] = $this->ticket->notify_email;

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

					$ticket_message->addAttachment($attach);

					$blob->is_temp = false;
					App::getOrm()->persist($blob);
				}
			}

			$this->new_message = $ticket_message;
			$ticket->addMessage($ticket_message);

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();

			$field_manager = App::getSystemService('ticket_fields_manager');
			$post_custom_fields = isset($_POST['newticket']['custom_ticket_fields']) ? $_POST['newticket']['custom_ticket_fields'] : array();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $ticket);
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();

			App::getOrm()->commit();

		} catch (\Exception $e) {
			App::getOrm()->rollback();
			throw $e;
		}

		return $ticket;
	}
}
