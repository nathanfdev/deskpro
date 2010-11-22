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
 * @Entity
 * @Table(name="tickets")
 */
class Ticket extends \DeskPRO\Domain\DomainObject
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
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="department_id", type="integer")
	 */
	protected $department_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Department
	 * @OneToOne(targetEntity="Department")
	 * @JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * @var int
	 * @Column(name="category_id", type="integer")
	 */
	protected $category_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\TicketCategory
	 * @OneToOne(targetEntity="TicketCategory")
	 * @JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category = null;

	/**
	 * @var int
	 * @Column(name="priority_id", type="integer")
	 */
	protected $priority_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\TicketPriority
	 * @OneToOne(targetEntity="TicketPriority")
	 * @JoinColumn(name="priority_id", referencedColumnName="id")
	 */
	protected $priority = null;

	/**
	 * @var int
	 * @Column(name="product_id", type="integer")
	 */
	protected $product_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Product
	 * @OneToOne(targetEntity="Product")
	 * @JoinColumn(name="product_id", referencedColumnName="id")
	 */
	protected $product = null;

	/**
	 * @var int
	 * @Column(name="person_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var int
	 * @Column(name="agent_id", type="integer")
	 */
	protected $agent_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Person
	 * @OneToOne(targetEntity="Person")
	 * @JoinColumn(name="agent_id", referencedColumnName="id")
	 */
	protected $agent = null;

	/**
	 * @var int
	 * @Column(name="organization_id", type="integer")
	 */
	protected $organization_id = null;

	/**
	 * @var \Application\CoreBundle\Entity\Organization
	 * @OneToOne(targetEntity="Organization")
	 * @JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization = null;

	/**
	 * @var string
	 * @Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system;

	/**
	 * @TODO Make this an enum type
	 * 
	 * @var string
	 * @Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @TODO Make this an enum type
	 *
	 * @var string
	 * @Column(name="hidden_status", type="string", length=15)
	 */
	protected $hidden_status;

	/**
	 * @var \DateTime
	 * @Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @Column(name="date_resolved",type="datetime",nullable=true)
	 */
	protected $date_resolved = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_closed",type="datetime",nullable=true)
	 */
	protected $date_closed = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_first_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_first_agent_reply = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_last_agent_reply",type="datetime",nullable=true)
	 */
	protected $date_last_agent_reply = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_last_user_reply",type="datetime",nullable=true)
	 */
	protected $date_last_user_reply = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_agent_waiting",type="datetime",nullable=true)
	 */
	protected $date_agent_waiting = null;

	/**
	 * @var \DateTime
	 * @Column(name="date_user_waiting",type="datetime",nullable=true)
	 */
	protected $date_user_waiting = null;

	/**
	 * @var int
	 * @Column(name="total_user_waiting", type="integer")
	 */
	protected $total_user_waiting = 0;

	/**
	 * @var int
	 * @Column(name="total_to_first_reply", type="integer")
	 */
	protected $total_to_first_reply = 0;

	/**
	 * @var int
	 * @Column(name="locked_by_agent", type="integer")
	 */
	protected $locked_by_agent = 0;

	/**
	 * @var \DateTime
	 * @Column(name="date_locked",type="datetime",nullable=true)
	 */
	protected $date_locked = null;

	/**
	 * @var bool
	 * @Column(name="has_attachments", type="boolean")
	 */
	protected $has_attachments = false;

	/**
	 * @var string
	 * @Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/** @PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}