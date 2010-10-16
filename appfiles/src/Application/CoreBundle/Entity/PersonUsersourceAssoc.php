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

/**
 * This tracks associations between a user and a usersource.
 */
class PersonUsersourceAssoc
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @var Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The usersource ID
	 * @var int
	 * @Column(name="usersource_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * The usersource that this scraper is attached to
	 *
	 * @var Usersource
	 * @OneToOne(targetEntity="Usersource")
	 * @JoinColumn(name="usersource_id", referencedColumnName="id")
	 */
	protected $usersource;

	/**
	 * The usersource ID
	 * @var int
	 * @Column(name="usersource_id", type="integer")
	 */
	protected $usersource_id;

	/**
	 * The remote users unique ID. This should change, so it's smart if this is a system
	 * ID such as a UserID.
	 *
	 * @var string
	 * @Index
	 * @Column(name="identity", type="string", length=255)
	 */
	protected $identity;

	/**
	 * The remote users friendly ID. This is what will be displayed in various interfaces.
	 * This should rarely change, like a username. Since we use $identity, we can handle
	 * if this changes.
	 *
	 * @var string
	 * @Index
	 * @Column(name="identity_friendly", type="string", length=255)
	 */
	protected $identity_friendly;

	/**
	 * Any raw data returned from the user auth adapter, it might contain useful information
	 * such as auth keys (eg: in twitter or facebook).
	 *
	 * @var array
	 * @Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * When the record was first created in the system
	 *
	 * @var \DateTime
	 * @Column(name="created_at", type="datetime")
	 */
	protected $created_at;

	/**
	 * The last time the user logged in using this auth.
	 *
	 * @var \DateTime
	 * @Column(name="last_used_at", type="datetime")
	 */
	protected $last_used_at;
}
