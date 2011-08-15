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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

/**
 * A log of deleted tickets
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="tickets_deleted")
 */
class TicketDeleted extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="new_ticket_id", type="integer")
	 */
	protected $new_ticket_id = 0;

	/**
	 * @var int
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="by_person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $by_person;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="reason", type="string", length=1000)
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