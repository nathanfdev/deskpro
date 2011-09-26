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
class NewTicket
{
	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\PersonProps
	 */
	public $person;

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\TicketProps
	 */
	public $ticket;

	public $custom_ticket_fields = array();

	public $new_message;

	public $creation_system;

	public function __construct($creation_system, Entity\Person $person = null)
	{
		if ($person AND !$person['id']) {
			$person = null;
		}

		$this->person = new PersonProps($person);
		$this->ticket = new TicketProps();

		for ($i = 0; $i < 100; $i++) {
			$this->custom_ticket_fields["field_$i"] = null;
		}

		$this->creation_system = $creation_system;
	}

	public function save()
	{
		App::getOrm()->beginTransaction();

		try {

			#------------------------------
			# Handle the person first
			#------------------------------

			$is_new_person = false;

			if ($this->person->person_obj) {
				$person = $this->person->person_obj;
			} else {

				// We might still have a person if the Contact is
				// is on record with an email addy, but still not a reg'd user
				$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($this->person->email);

				if (!$person) {
					$person = Entity\Person::newContactPerson();
				}
			}

			if ($this->person->name) {
				$person['name'] = $this->person->name;
			}

			App::getOrm()->persist($person);

			// Note that dupe emails shouldnt happen here
			// The person should already be a person who
			// has the address, or else we need to create the address as
			// an unvalidated address and require them to validate now
			$email = $person->findEmailAddress($this->person->email);
			$email_validating = null;
			if (!$email) {
				$email_validating = new Entity\PersonEmailValidating();
				$email_validating->email = $this->person->email;
				$email_validating->person = $person;
				App::getOrm()->persist($email_validating);
			}

			#------------------------------
			# Now ticket
			#------------------------------

			$ticket = new Entity\Ticket();
			$ticket['creation_system']  = $this->creation_system;
			$ticket['person']  = $person;
			$ticket['subject'] = $this->ticket->subject;

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

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			$raw_custom_fields = isset($_POST['newticket']['custom_fields']) ? $_POST['newticket']['custom_fields'] : array();
			foreach ($ticket_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($raw_custom_fields) as $info) {
					$ticket->setCustomData($info[0], $info[1], $info[2]);
				}
			}

			if ($dupe_ticket = App::getEntityRepository('DeskPRO:Ticket')->checkDupeTicket($ticket)) {
				App::getOrm()->rollback();
				return $dupe_ticket;
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
