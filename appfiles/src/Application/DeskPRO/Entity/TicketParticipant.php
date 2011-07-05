<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Orb\Util\Strings;

use Application\DeskPRO\App;

/**
 * Links participants to tickets
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="tickets_participants")
 */
class TicketParticipant extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Ticket", inversedBy="participants")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 * @orm:Id
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketAccessCode
	 * @orm:OneToOne(targetEntity="TicketAccessCode", fetch="EAGER", cascade={"persist", "remove", "merge"})
	 * @orm:JoinColumn(name="access_code_id", referencedColumnName="id")
	 */
	protected $access_code = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @orm:ManyToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @orm:JoinColumn(name="person_email_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person_email = null;

	/**
	 * Default checkbox status of the user
	 * 
	 * @var bool
	 * @orm:Column(name="default_on", type="boolean")
	 */
	protected $default_on = true;

	public function __construct()
	{

	}

	public function setPerson(Person $person)
	{
		if ($this->person == $person) {
			return;
		}

		$this->_onPropertyChanged('person', $this->person, $person);
		$this->person = $person;

		if (!$this->person_email && $this->person->primary_email) {
			$this->setPersonEmail($this->person->primary_email);
		}
	}

	public function setPersonId($id)
	{
		$person = App::findEntity('DeskPRO:Person', $id);
		$this->setPerson($person);
	}

	public function setPersonEmailId($id)
	{
		$person_email = App::findEntity('DeskPRO:PersonEmail', $id);
		$this['person_email'] = $person_email;
	}

	public function getEmailAddress()
	{
		return $this->person_email['email'];
	}

	/**
	 * @orm:PrePersist
	 */
	public function _setAccessCode()
	{
		if (!$this->access_code) {

			// try to find an existing TAC for this person and ticket,
			// ie agents may already have one from them getting notifications
			
			$access_code = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByTicketAndPerson($this->ticket, $this->person);
			if (!$access_code) {
				$access_code = new TicketAccessCode();
			}

			$this->access_code = $access_code;
		}

		$this->access_code->person = $this->person;
		$this->access_code->ticket = $this->ticket;
	}
}