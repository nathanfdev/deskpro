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
 * @orm:Entity
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
	 * @var int
	 * @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * Who created the attachment
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var int
	 * @orm:Column(name="blob_id", type="integer")
	 */
	protected $blob_id;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:ManyToOne(targetEntity="Blob")
	 * @orm:JoinColumn(name="blob_id", referencedColumnName="id")
	 */
	protected $blob;

	/**
	 * @var int
	 * @orm:Column(name="message_id", type="integer", nullable=true)
	 */
	protected $message_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @orm:ManyToOne(targetEntity="TicketMessage")
	 * @orm:JoinColumn(name="message_id", referencedColumnName="id")
	 */
	protected $message = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}