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
 * @orm:Table(name="tickets_participants")
 */
class TicketParticipant extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Ticket", inversedBy="participants")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @orm:ManyToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @orm:JoinColumn(name="person_email_id", referencedColumnName="id")
	 */
	protected $person_email = null;

	/**
	 * @var string
	 * @orm:Column(name="code", type="string", length=12)
	 */
	protected $code = null;

	/**
	 * Default checkbox status of the user
	 * 
	 * @var bool
	 * @orm:Column(name="default_on", type="boolean")
	 */
	protected $default_on = true;

	public function __construct()
	{
		$this->code = Strings::random(12, Strings::CHARS_KEY);
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
}