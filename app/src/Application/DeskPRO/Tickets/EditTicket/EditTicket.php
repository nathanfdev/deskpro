<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\Tickets\EditTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\Layout;

class EditTicket implements \Application\DeskPRO\People\PersonContextInterface, \ArrayAccess
{
	/** @var array */
	protected static $prop_names = array('person' => 1, 'ticket' => 1, 'custom_ticket_fields' => 1);

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\PersonProps
	 */
	public $person;

	/**
	 * The person who is running this (ex an agent?)
	 */
	protected $person_context;

	/**
	 * The actual ticket
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket_object;

	/**
	 * @var \Application\DeskPRO\Tickets\EditTicket\EditTicketProps
	 */
	public $ticket;

	/** @var array */
	public $custom_ticket_fields = array();
	/** @var array */
	public $custom_user_fields = array();

	/**
	 * @var array
	 */
	protected $display_fields = array();

	public function setLayout(Layout $layout)
	{
		foreach ($layout as $field) {
			$this->display_fields[$field->getId()] = $field->getId();
		}
	}

	public function __construct(Ticket $ticket_object)
	{
		$this->ticket_object = $ticket_object;
		$this->ticket = new EditTicketProps($ticket_object);

		for ($i = 0; $i < 500; $i++) {
			$this->custom_ticket_fields["field_$i"] = null;
		}
		for ($i = 0; $i < 500; $i++) {
			$this->custom_user_fields["field_$i"] = null;
		}
	}

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	public function save()
	{
		App::getDb()->beginTransaction();
		try {
			if (isset($this->display_fields['subject'])) {
				$this->ticket_object->subject = $this->ticket->subject;
			}
			if (isset($this->display_fields['department'])) {
				$this->ticket_object->department = $this->ticket->department_id ? App::findEntity('DeskPRO:Department', $this->ticket->department_id) : null;
			}
			if (isset($this->display_fields['category'])) {
				$this->ticket_object->category   = $this->ticket->category_id ? App::findEntity('DeskPRO:TicketCategory', $this->ticket->category_id) : null;
			}
			if (isset($this->display_fields['priority'])) {
				$this->ticket_object->priority   = $this->ticket->priority_id ? App::findEntity('DeskPRO:TicketPriority', $this->ticket->priority_id) : null;
			}
			if (isset($this->display_fields['product'])) {
				$this->ticket_object->product    = $this->ticket->product_id ? App::findEntity('DeskPRO:Product', $this->ticket->product_id) : null;
			}

			$field_manager = App::getSystemService('ticket_fields_manager');
			$post_custom_fields = App::getRequest()->request->get('custom_fields', array());
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $this->ticket_object);
			}

			$new_field_manager = App::$container->getCustomFieldManager();
			$new_field_manager->flush();

			$user_field_manager = App::getSystemService('person_fields_manager');
			$post_custom_fields = App::getRequest()->request->get('custom_user_fields', array());
			if (!empty($post_custom_fields)) {
				$user_field_manager->saveFormToObject($post_custom_fields, $this->ticket_object->person);
			}

			if ($this->ticket->remove_ccs) {
				foreach ($this->ticket->remove_ccs AS $remove_person_id) {
					$this->ticket_object->removeParticipantPerson($remove_person_id);
				}
			}

			App::getOrm()->persist($this->ticket_object);
			App::getOrm()->persist($this->ticket_object->person);
			App::getOrm()->flush();

			if ($this->ticket->cc_emails) {
				$ccs = explode(',', $this->ticket->cc_emails);

				foreach ($ccs as &$_) {
					$_ = trim(strtolower($_));
					if (!\Orb\Validator\StringEmail::isValueValid($_) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($_)) {
						$_ = null;
					}
				}

				$ccs = array_unique($ccs);
				$ccs = \Orb\Util\Arrays::removeFalsey($ccs);

				if ($ccs) {
					foreach ($ccs as $cc) {
						$this->handleCc($this->ticket_object, $cc);
					}
					App::getOrm()->flush();
				}
			}

			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}
	}

	public function handleCc(Ticket $ticket, $cc_email)
	{
		if (!\Orb\Validator\StringEmail::isValueValid($cc_email)) {
			return null;
		}

		$account_manager = App::$container->getEmailAccountManager();
		if ($account_manager->findAccountForEmailAddress($cc_email)) {
			return null;
		}

		$person_processor = new \Application\DeskPRO\EmailGateway\PersonFromEmailProcessor();

		$cc = new \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress();
		$cc->email = $cc_email;
		$cc->name = '';
		$cc->name_utf8 = '';

		$cc_person = $person_processor->findPerson($cc);
		if (!$cc_person) {
			// Closed helpdesk and an unknown CC means we drop it
			if (!App::getContainer()->getSetting('core.reg_enabled')) {
				return null;
			}

			$cc_person = Person::newContactPerson();
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

    public function offsetExists($offset)        { return (isset(self::$prop_names[$offset]) && isset($this->$offset)); }
    public function offsetGet($offset)           { if (isset(self::$prop_names[$offset])) return $this->$offset; }
    public function offsetSet($offset, $value)   { if (isset(self::$prop_names[$offset])) $this->$offset = $value; }
    public function offsetUnset($offset)         { if (isset(self::$prop_names[$offset])) $this->$offset = null; }
}
