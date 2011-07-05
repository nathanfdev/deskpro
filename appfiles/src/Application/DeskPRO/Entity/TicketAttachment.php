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

/**
 * Ticket attachments
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketAttachment")
 * @orm:Table(name="tickets_attachments")
 */
class TicketAttachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket;

	/**
	 * Who created the attachment
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @orm:JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @orm:ManyToOne(targetEntity="TicketMessage", fetch="EAGER")
	 * @orm:JoinColumn(name="message_id", referencedColumnName="id")
	 */
	protected $message = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}