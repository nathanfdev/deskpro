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

	public $creation_system;

	public function __construct($creation_system, Entity\Person $person = null)
	{
		if ($person AND !$person['id']) {
			$person = null;
		}

		$this->person = new PersonProps($person);
		$this->ticket = new TicketProps();

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
					$is_new_person = true;
				}
			}

			$person['first_name'] = $this->person->first_name;
			$person['last_name'] = $this->person->last_name;

			// Note that dupe emails shouldnt happen here
			// The person should already be a person who
			// has the address, or else we're just initializing it now

			$email = $person->findEmailAddress($this->person->email);
			if (!$email) {
				$email = new Entity\PersonEmail();
				$email['email'] = $this->person->email;
				$email['is_validated'] = false;

				$person->addEmailAddress($email);
			}
			App::getOrm()->persist($person);
			App::getOrm()->flush();

			#------------------------------
			# Now ticket
			#------------------------------

			$ticket = new Entity\Ticket();
			$ticket['creation_system']  = $this->creation_system;
			$ticket['person']  = $person;
			$ticket['subject'] = $this->ticket->subject;
			$ticket['person_email'] = $email;

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

			if ($id = App::getEntityRepository('DeskPRO:TicketMessage')->checkDupeMessage($ticket_message)) {
				$ticket_message = App::findEntity('DeskPRO:TicketMessage', $id);
				return $ticket_message->ticket;
			}

			$ticket->addMessage($ticket_message);

			if ($ticket->person_email['is_validated']) {
				$ticket['status']        = Entity\Ticket::STATUS_OPEN;
			} else {
				$ticket['status']        = Entity\Ticket::STATUS_HIDDEN;
				$ticket['hidden_status'] = Entity\Ticket::HIDDEN_STATUS_VALIDATING;
			}

			$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
			$raw_custom_fields = isset($_POST['newticket']['custom_fields']) ? $_POST['newticket']['custom_fields'] : array();
			foreach ($ticket_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($raw_custom_fields) as $info) {
					$ticket->setCustomData($info[0], $info[1], $info[2]);
				}
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