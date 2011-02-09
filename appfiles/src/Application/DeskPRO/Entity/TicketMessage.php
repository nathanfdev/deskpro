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
 * Ticket messages
 *
 * @orm:Entity
 * @orm:Table(name="tickets_messages")
 */
class TicketMessage extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="ticket_id", type="integer")
	 */
	protected $ticket_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @orm:OneToMany(targetEntity="TicketAttachment", mappedBy="message", cascade={"persist", "remove", "merge"})
	 */
	protected $attachments;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var bool
	 * @orm:Column(name="is_agent_note", type="boolean")
	 */
	protected $is_agent_note = false;

	/**
	 * @var string
	 * @orm:Column(name="message_hash", type="string", length=40)
	 */
	protected $message_hash;

	/**
	 * The message, will be in HTML!
	 * @var string
	 * @orm:Column(name="message", type="text")
	 */
	protected $message;

	public function getMessageHtml()
	{
		return $this->message;
	}

	public function getMessageText()
	{
		$message = $this->message;
		$message = strip_tags($message);
		return $message;
	}

	public function getMessagePlainHtml()
	{
		return nl2br($this->getMessageText());
	}

	public function setMessage($message)
	{
		$this->message = $message;
		$this->message_hash = sha1($message);
	}

	public function addAttachment(TicketAttachment $attach)
	{
		$this->attachments->add($attach);
		$attach['ticket'] = $this->ticket;
		$attach['message'] = $this;
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
	}
}