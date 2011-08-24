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
use Application\DeskPRO\Markdown;

/**
 * Ticket messages
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketMessage")
 * @ORM_Mapping\Table(name="tickets_messages")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TicketMessage extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB = 'web';
	const CREATED_GATEWAY = 'gateway';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @ORM_Mapping\ManyToOne(targetEntity="Ticket")
	 * @ORM_Mapping\JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailSource
	 * @ORM_Mapping\OneToOne(targetEntity="EmailSource")
	 * @ORM_Mapping\JoinColumn(name="email_source_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $email_source = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="TicketAttachment", mappedBy="message", cascade={"persist", "remove", "merge"})
	 */
	protected $attachments;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent_note", type="boolean")
	 */
	protected $is_agent_note = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system = 'web';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

	/**
	 * The email address the user sent the email from (gateway messages only).
	 * This is a perm record and doesnt change even if the user changes/deletes their email
	 * address.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255)
	 */
	protected $email = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="message_hash", type="string", length=40)
	 */
	protected $message_hash;

	/**
	 * The message, will be in HTML!
	 * @var string
	 * @ORM_Mapping\Column(name="message", type="text")
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

	public function setVisitorFromRequest()
	{
		if (App::has('session')) {
			$v = App::getSession()->getVisitor();
			$this->setVisitor($v);
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

	public function getMessageHash()
	{
		if (!$this->message_hash) {
			$this->initHashCode();
		}

		return $this->message_hash;
	}

	/**
	 * Inits the hash code for this message
	 *
	 * @ORM_Mapping\PrePersist
	 */
	public function initHashCode()
	{
		if ($this->message_hash) {
			return;
		}

		$hashes = array();
		$hashes[] = sha1($this->message . $this->person->id);

		foreach ($this->attachments as $a) {
			$hashes[] = $a->blob['blob_hash'];
		}

		// Sort hashes so theyre always the same order
		sort($hashes, \SORT_STRING);

		$this->message_hash = sha1(implode('', $hashes));
		$this->_onPropertyChanged('message_hash', '', $this->message_hash);
	}

	/**
	 * When a new message is added to a ticket, make sure the person has
	 * their own access code ready to use.
	 *
	 * @ORM_Mapping\PostPersist
	 */
	public function initPersonAccessCode()
	{
		if ($this->id) {
			App::getEntityRepository('DeskPRO:Cache')->delete("ticket_messages.{$this->ticket['id']}");
		}

		$this->ticket->addAccessCodeForPerson($this->person);
	}
}
