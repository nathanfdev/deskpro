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
 * This tracks associations between a user and a usersource.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\PersonUsersourceAssoc")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="person_usersource_assoc")
 */
class PersonUsersourceAssoc extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * The usersource ID
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The usersource ID
	 * @var int
	 * @orm:Column(name="usersource_id", type="integer")
	 */
	protected $usersource_id;

	/**
	 * The usersource that this scraper is attached to
	 *
	 * @var Usersource
	 * @orm:OneToOne(targetEntity="Usersource")
	 * @orm:JoinColumn(name="usersource_id", referencedColumnName="id")
	 */
	protected $usersource;

	/**
	 * The remote users unique ID. This should not change, so it's smart if this is a system
	 * ID such as a UserID.
	 *
	 * @var string
	 * @orm:Index
	 * @orm:Column(name="identity", type="string", length=255)
	 */
	protected $identity;

	/**
	 * The remote users friendly ID. This is what will be displayed in various interfaces.
	 * This should rarely change, like a username. Since we use $identity, we can handle
	 * if this changes.
	 *
	 * @var string
	 * @orm:Index
	 * @orm:Column(name="identity_friendly", type="string", length=255)
	 */
	protected $identity_friendly;

	/**
	 * Any raw data returned from the user auth adapter, it might contain useful information
	 * such as auth keys (eg: in twitter or facebook).
	 *
	 * @var array
	 * @orm:Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * When the record was first created in the system
	 *
	 * @var \DateTime
	 * @orm:Column(name="created_at", type="datetime")
	 */
	protected $created_at;

	/**
	 * The last time the user logged in using this auth.
	 *
	 * @var \DateTime
	 * @orm:Column(name="last_used_at", type="datetime")
	 */
	protected $last_used_at;


	
	/**
	 * Set the last time this usersource was used.
	 *
	 * @param DateTime $time The time to set, or null to set now
	 */
	public function setLastUsedAt(\DateTime $time = null)
	{
		if (!$time) $time = new \DateTime();
		
		$this->last_used_at = $time;
	}


	/** @orm:PrePersist */
	public function _incCreatedAt()
	{
		if (!$this->created_at) {
			$this->created_at = new \DateTime();
		}
		$this->setLastUsedAt();
	}
}
