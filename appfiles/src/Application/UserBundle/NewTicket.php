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

namespace Application\UserBundle;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

/**
 * This object handles taking form input values and then after validation,
 * properly creates the ticket and (if needed), the user too.
 */
class NewTicket
{
	public $ticket_values = array();
	public $ticket_custom_values = array();
	public $person_values = array();
	public $person_custom_values = array();
	public $ticket_message = '';

	/**
	 * Handled specially because its an option if the person is logged in/exists,
	 * or we can be creating it from scratch
	 *
	 * @var string
	 */
	public $email_address = '';

	public $person;
	public $is_new_person = false;
	public $ticket;

	public function setPerson(Entity\Person $person)
	{
		if (!$person['id']) return;

		$this->person = $person;

		$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$this->person_custom_values = App::getApi('custom_fields.util')->createFormData($person['custom_data'], $field_defs);
	}



	/**
	 * Save the new ticket
	 *
	 * @return void
	 */
	public function save()
	{
		App::getOrm()->beginTransaction();

		#------------------------------
		# First take care of the person
		#------------------------------

		$this->is_new_person = false;
		if (!$this->person) {
			$this->person = Entity\Person::newContactPerson();
			$this->is_new_person = true;
		}

		$this->person->fromArray($this->person_values);
		if ($this->person_custom_values) {
			$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
			foreach ($field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($this->person_custom_values) as $info) {
					$this->person->setCustomData($info[0], $info[1], $info[2]);
				}
			}
		}

		App::getOrm()->persist($this->person);
		App::getOrm()->flush();


		#------------------------------
		# Save the ticket
		#------------------------------

		$this->ticket = new Entity\Ticket();
		$this->ticket->fromArray($this->ticket_values);

		if ($this->person['is_confirmed']) {
			$this->ticket['status'] = Entity\Ticket::STATUS_HIDDEN;
			$this->ticket['hidden_status'] = Entity\Ticket::HIDDEN_STATUS_VALIDATING;
		} else {
			$this->ticket['status'] = Entity\Ticket::STATUS_OPEN;
		}
		$this->ticket['person'] = $this->person;
		$this->ticket['creation_system'] = Entity\Ticket::CREATED_WEB_PERSON;

		App::getOrm()->persist($this->ticket);
		App::getOrm()->flush();

		$message = new Entity\TicketMessage();
		$message['person'] = $this->person;
		$message['message'] = $this->message;
		$this->ticket->addMessage($message);

		App::getOrm()->persist($message);
		App::getOrm()->flush();

		App::getOrm()->commit();
	}
}