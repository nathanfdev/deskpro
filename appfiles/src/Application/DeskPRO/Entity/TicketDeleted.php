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

use Application\DeskPRO\App;

/**
 * A log of deleted tickets
 *
 * @orm:Entity
 * @orm:Table(name="tickets_deleted")
 */
class TicketDeleted extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id
	 * @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @orm:Id
	 * @orm:Column(name="new_ticket_id", type="integer")
	 */
	protected $new_ticket_id = 0;

	/**
	 * @var int
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="by_person_id", referencedColumnName="id")
	 */
	protected $by_person;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var string
	 * @orm:Column(name="reason", type="string", length=1000)
	 */
	protected $reason;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}


	public function getByPersonId()
	{
		if ($this->by_person) {
			return $this->by_person['id'];
		}

		return 0;
	}

	public function setByPersonId($id)
	{
		if ($id) {
			$this->by_person = App::getEntityRepository('DeskPRO:Person')->find($id);
		} else {
			$this->by_person = null;
		}
	}
}