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

namespace DeskPRO\Entities;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A "profile" is a record in the database that stores information about a person.
 *
 * @Entity
 * @Table(name="profiles")
 */
class Profile extends Entity
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The user this email belongs to if any
	 *
	 * @var int
	 * @Column(name="user_id", type="integer", nullable=true)
	 */
	protected $user_id = null;


	/**
	 * @var User
	 * @OneToOne(targetEntity="User", mappedBy="profile")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user = null;


	/**
	 * The users full name.
	 *
	 * @var string
	 * @Column(name="full_name", type="text", nullable=true)
	 */
	protected $fullname = null;


	/**
	 * What the user wants to be called. For example, a first name. This
	 * is used in greetings when available.
	 *
	 * @var string
	 * @Column(name="informal_name", type="text", nullable=true)
	 */
	protected $informal_name = null;


	/**
	 * A secret string used in various hashing or encryption schemes.
	 *
	 * @var string
	 * @Column(name="secret_string", type="string", length=40)
	 */
	protected $secret_string;


	/**
	 * The users locale/language
	 *
	 * @var string
	 * @Column(name="locale", type="string", length=10, nullable=true)
	 */
	protected $locale;


	/**
	 * The timezone associated with this user.
	 *
	 * @var string
	 * @Column(name="timezome", type="string", length=30)
	 */
	protected $timezone;


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;


	/**
	 * @var ArrayCollection
	 * @OneToMany(targetEntity="ProfileEmail", mappedBy="profile")
	 */
	protected $email_addresses;



	public function __construct()
	{
		$this->created_at = new \DateTime();
		$this->updated_at = new \DateTime();
		$this->secret_string = Strings::random(40);
		$this->timezone = 'UTC';

		$this->email_addresses = new \Doctrine\Common\Collections\ArrayCollection();
	}

	

	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}