<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * A log of deleted tickets
 *
 * @Entity
 * @Table(name="tickets_deleted")
 */
class TicketDeleted extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @Id @Column(name="new_ticket_id", type="integer")
	 */
	protected $new_ticket_id = 0;

	/**
	 * @var int
	 * @Id @Column(name="by_person_id", type="integer")
	 */
	protected $by_person_id;

	/**
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var string
	 * @Column(name="reason", type="string", length=1000)
	 */
	protected $reason;

	/** @PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}