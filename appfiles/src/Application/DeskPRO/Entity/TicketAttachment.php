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

/**
 * Ticket attachments
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketAttachment")
 * @ORM_Mapping\Table(name="tickets_attachments")
 */
class TicketAttachment extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket;

	/**
	 * Who created the attachment
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;

	/**
	 * @var \Application\DeskPRO\Entity\TicketMessage
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketMessage", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="message_id", referencedColumnName="id")
	 */
	protected $message = null;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent_note", type="boolean")
	 */
	protected $is_agent_note = false;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}


	/**
	 * @param $message
	 */
	public function setMessage($message)
	{
		$this->setModelField('message', $message);

		// Automatically set the is_agent_note field
		if ($message->is_agent_note) {
			$this->is_agent_note = true;
		}
	}
}
