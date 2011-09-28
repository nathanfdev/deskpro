<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\NewTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * New ticket acts as the processor and domain object for a newticket form
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

	public $is_html = false;

	protected $mode = 'untrusted';

	public function __construct($creation_system, Entity\Person $person = null)
	{
		if ($person AND !$person['id']) {
			$person = null;
		}

		$this->person = new PersonProps($person);
		$this->ticket = new TicketProps();

		for ($i = 0; $i < 500; $i++) {
			$this->custom_ticket_fields["field_$i"] = null;
		}

		$this->creation_system = $creation_system;
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

				if ($email) {
					$validating = 'existing';

					$person = $email->person;

					$email_validating = new Entity\PersonEmailValidating();
					$email_validating->email = $email->email;
					$email_validating->person = $person;
					App::getOrm()->persist($email_validating);

				} else {
					$validating = 'new';

					$email_validating = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getEmail($this->person->email);

					if (!$email_validating) {
						$person = Entity\Person::newContactPerson();
						$person->name = $this->person->name;
						App::getOrm()->persist($person);

						$email_validating = new Entity\PersonEmailValidating();
						$email_validating->email = $this->person->email;
						$email_validating->person = $person;
						App::getOrm()->persist($email_validating);

					} else {
						$person = $email_validating->person;
					}
				}
			} else {
				$person = $this->person_context;

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
			$ticket['creation_system']  = $this->creation_system;
			$ticket['person']  = $person;
			$ticket['subject'] = $this->ticket->subject;
			$ticket['validating'] = $validating;

			if ($email_validating) {
				$ticket->person_email_validating = $email_validating;
			} else {
				$ticket['person_email'] = $email;
			}
			$ticket['status'] = 'open';

			foreach (array('department_id', 'category_id', 'product_id', 'priority_id') as $prop) {
				$ticket[$prop] = $this->ticket->$prop;
			}

			$ticket_message = new Entity\TicketMessage();
			$ticket_message['person']  = $person;
			$ticket_message['ticket']  = $ticket;
			$ticket_message['message'] = $this->ticket->message;
			if (!$ticket_message['message']) {
				$ticket_message['message'] = '(no message)';
			}

			if (!$this->is_html) {
				$ticket_message['message'] = htmlspecialchars($ticket_message['message']);
			}

			$attach = false;
			if ($this->ticket->new_upload) {
				$desc = App::getApi('filestorage')->createRandomPath();

				$desc->write(file_get_contents($this->ticket->new_upload->getRealPath()), array(
					'content_type' => $this->ticket->new_upload->getMimeType(),
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
					if ($this->ticket->attach_ids_authed) {
						list($blob_id, $blob_auth) = explode('-', $blob_auth_id, 2);
						$blob = App::findEntity('DeskPRO:Blob', $blob_id);
						if ($blob && $blob->getAuthCode() != $blob_auth) {
							$blob = false;
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

			$this->new_message = $ticket_message;
			$ticket->addMessage($ticket_message);

			if ($email_validating) {
				$ticket['status'] = 'hidden.validating';
			} else {
				$ticket['status'] = 'open';
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			$raw_custom_fields = isset($_POST['newticket']['custom_ticket_fields']) ? $_POST['newticket']['custom_ticket_fields'] : array();
			foreach ($ticket_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($raw_custom_fields) as $info) {
					$ticket->setCustomData($info[0], $info[1], $info[2]);
					App::getOrm()->flush();
				}
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();

			//if ($dupe_ticket = App::getEntityRepository('DeskPRO:Ticket')->checkDupeTicket($ticket)) {
			//	App::getOrm()->rollback();
			//	return $dupe_ticket;
			//}

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
