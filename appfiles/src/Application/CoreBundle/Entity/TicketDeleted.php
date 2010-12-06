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
 * @orm:Entity
 * @orm:Table(name="tickets_deleted")
 */
class TicketDeleted extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="new_ticket_id", type="integer")
	 */
	protected $new_ticket_id = 0;

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="by_person_id", type="integer")
	 */
	protected $by_person_id;

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

	/** @orm:PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}