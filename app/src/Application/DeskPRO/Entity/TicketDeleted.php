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
	 * @ORM_Mapping\Column(name="reason", type="text")
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
