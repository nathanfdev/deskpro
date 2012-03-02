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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Orb\Util\Strings;

use Application\DeskPRO\App;

/**
 * Links participants to tickets
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="tickets_participants")
 */
class TicketParticipant extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket", inversedBy="participants")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 * @ORM_Mapping\Id
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketAccessCode
	 * @ORM_Mapping\OneToOne(targetEntity="TicketAccessCode", fetch="EAGER", cascade={"persist", "remove", "merge"})
	 * @ORM_Mapping\JoinColumn(name="access_code_id", referencedColumnName="id")
	 */
	protected $access_code = null;

	/**
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @ORM_Mapping\ManyToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_email_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person_email = null;

	/**
	 * Default checkbox status of the user
	 * 
	 * @var bool
	 * @ORM_Mapping\Column(name="default_on", type="boolean")
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
	 * @ORM_Mapping\PrePersist
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
