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
 * Ticket
 *
 * @orm:Entity
 * @orm:Table(name="tickets")
 */
class Ticket extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB_PERSON = 'web_person';
	const CREATED_WEB_AGENT = 'web_agent';
	const CREATED_GATEWAT_PERSON = 'gateway_person';

	const STATUS_AWAITING_AGENT = 'awaiting_agent';
	const STATUS_AWAITING_USER = 'awaiting_user';
	const STATUS_RESOLVED = 'resolved';
	const STATUS_CLOSED = 'closed';
	
	const HIDDEN_STATUS_SPAM = 'spam';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The language ID.
	 *
	 * @var int
	 * @orm:Column(name="language_id", type="integer", nullable=true)
	 */
	protected $language_id = null;

	/**
	 * The language associate with the user.
	 *
	 * @var \Application\CoreBundle\Entity\Language
	 * @orm:ManyToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language = null;

	/**
	 * @var int
	 * @orm:Column(name="department_id", type="integer")
	 */
	protected $department_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var int
	 * @orm:Column(name="category_id", type="integer")
	 */
	protected $category_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\TicketCategory
	 * @orm:ManyToOne(targetEntity="TicketCategory")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category = null;

	/**
	 * @var int
	 * @orm:Column(name="priority_id", type="integer")
	 */
	protected $priority_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\TicketPriority
	 * @orm:ManyToOne(targetEntity="TicketPriority")
	 * @orm:JoinColumn(name="priority_id", referencedColumnName="id")
	 */
	protected $priority = null;

	/**
	 * @var int
	 * @orm:Column(name="product_id", type="integer")
	 */
	protected $product_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Product
	 * @orm:ManyToOne(targetEntity="Product")
	 * @orm:JoinColumn(name="product_id", referencedColumnName="id")
	 */
	protected $product = null;

	/**
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var int
	 * @orm:Column(name="agent_id", type="integer")
	 */
	protected $agent_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent = null;

	/**
	 * @var int
	 * @orm:Column(name="organization_id", type="integer")
	 */
	protected $organization_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization = null;

	/**
	 * @orm:OneToMany(targetEntity="TicketMessage", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $messages;

	/**
	 * @var string
	 * @orm:Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system;

	/**
	 * @TODO Make this an enum type
	 * 
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @TODO Make this an enum type
	 *
	 * @var string
	 * @orm:Column(name="hidden_status", type="string", length=15)
	 */
	protected $hidden_status;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_resolved",type="datetime",nullable=true)
	 */
	protected $date_resolved = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_closed",type="datetime",nullable=true)
	 */
	protected $date_closed = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_first_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_first_agent_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_last_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_last_agent_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_last_user_reply",type="datetime",nullable=true)
	 */
	protected $date_last_user_reply = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_agent_waiting",type="datetime",nullable=true)
	 */
	protected $date_agent_waiting = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_user_waiting",type="datetime",nullable=true)
	 */
	protected $date_user_waiting = null;

	/**
	 * @var int
	 * @orm:Column(name="total_user_waiting", type="integer")
	 */
	protected $total_user_waiting = 0;

	/**
	 * @var int
	 * @orm:Column(name="total_to_first_reply", type="integer")
	 */
	protected $total_to_first_reply = 0;

	/**
	 * @var int
	 * @orm:Column(name="locked_by_agent", type="integer")
	 */
	protected $locked_by_agent = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_locked",type="datetime",nullable=true)
	 */
	protected $date_locked = null;

	/**
	 * @var bool
	 * @orm:Column(name="has_attachments", type="boolean")
	 */
	protected $has_attachments = false;

	/**
	 * @var string
	 * @orm:Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TicketParticipant", mappedBy="ticket", cascade={"persist", "remove", "merge"})
	 */
	protected $participants;

	public function __construct()
	{
		$this->participants = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * Get a simple array of person ID's of participants.
	 *
	 * @return array 
	 */
	public function getParticipantIds()
	{
		$ids = array();
		foreach ($this->participants as $p) {
			$ids[] = $p['person_id'];
		}

		return $ids;
	}


	
	/**
	 * Check if a person ID or a person object is current a participant.
	 *
	 * @param  $person_or_id
	 * @return bool
	 */
	public function hasParticipant($person_or_id)
	{
		$person_id = $person_or_id;
		if ($person_or_id instanceof Person) {
			$person_id = $person_or_id['id'];
		}

		foreach ($this->participants as $p) {
			if ($p['person_id'] == $person_id) {
				return true;
			}
		}

		return false;
	}


	
	/** @orm:PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}