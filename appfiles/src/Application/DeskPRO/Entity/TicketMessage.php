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
use Application\DeskPRO\Markdown;

/**
 * Ticket messages
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketMessage")
 * @orm:Table(name="tickets_messages")
 * @orm:HasLifecycleCallbacks
 */
class TicketMessage extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB = 'web';
	const CREATED_GATEWAY = 'gateway';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;
	
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailSource
	 * @orm:OneToOne(targetEntity="EmailSource")
	 * @orm:JoinColumn(name="email_source_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $email_source = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

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
	 * @orm:Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system = 'web';

	/**
	 * @var string
	 * @orm:Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

	/**
	 * The email address the user sent the email from (gateway messages only).
	 * This is a perm record and doesnt change even if the user changes/deletes their email
	 * address.
	 *
	 * @var string
	 * @orm:Column(name="email", type="string", length=255)
	 */
	protected $email = '';

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

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function setTicketId($id)
	{
		$this->ticket = App::getEntityRepository('DeskPRO:Ticket')->find($id);
	}

	public function getTicketId()
	{
		return $this->ticket['id'];
	}

	public function setPersonId($id)
	{
		$this->person = App::getEntityRepository('DeskPRO:Person')->find($id);
	}

	public function getPersonId()
	{
		return $this->person['id'];
	}

	public function getMessageHtml()
	{
		return $this->message;
	}

	public function getMessageText()
	{
		$message = $this->message;
		$message = strip_tags($message);

		// Decode entities in the HTML back to characters,
		// This is needed so when outputting, they arent double-encoded by twig
		// (And its just proper!)
		$message = html_entity_decode($message);
		
		return $message;
	}

	public function getMessagePlainHtml()
	{
		return nl2br($this->getMessageText());
	}

	public function setMessageHtml($message)
	{
		$this->setMessage($message);
	}

	public function setMessageText($message)
	{
		$this->setMessage(Markdown::format($message));
	}

	public function setMessage($message)
	{
		$this->message = $message;
		$this['message_hash'] = sha1($message);
	}

	public function addAttachment(TicketAttachment $attach)
	{
		$this->attachments->add($attach);
		$attach['ticket'] = $this->ticket;
		$attach['message'] = $this;
	}

	public function setVisitor(Visitor $visitor = null)
	{
		$this->_onPropertyChanged('visitor', $this->visitor, $visitor);
		$this->visitor = $visitor;

		if ($visitor === null) return;

		if (!$this->ip_address) {
			$this['ip_address'] = $visitor['ip_address'];
		}
	}

	/**
	 * Did this message originate from a gateway?
	 *
	 * @return bool
	 */
	public function isFromGateway()
	{
		if (strpos($this->creation_system, 'gateway') === 0) {
			return true;
		}

		return false;
	}

	/**
	 * When a new message is added to a ticket, make sure the person has
	 * their own access code ready to use.
	 * 
	 * @orm:PostPersist
	 */
	public function initPersonAccessCode()
	{
		if ($this->id) {
			App::getEntityRepository('DeskPRO:Cache')->delete("ticket_messages.{$this->ticket['id']}");
		}

		$this->ticket->addAccessCodeForPerson($this->person);
	}
}