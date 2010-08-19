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

namespace DeskPRO\Bundle\Core\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Email addresses attached to a profile
 *
 * @Entity
 * @Table(name="profile_emails")
 */
class ProfileEmail extends \DeskPRO\Bundle\Core\Entity\Entity
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
	 * The profile this email belongs to
	 *
	 * @var int
	 * @Column(name="profile_id", type="integer")
	 */
	protected $profile_id;


	/**
	 * @var Profile
	 * @ManyToOne(targetEntity="Profile", inversedBy="email_addresses")
	 * @JoinColumn(name="profile_id", referencedColumnName="id")
	 */
	protected $profile;


	/**
	 * The email address
	 *
	 * @var string
	 * @Column(name="email_address", type="string", length=255)
	 */
	protected $email_address;


	/**
	 * @var bool
	 * @Column(name="is_validated", type="boolean")
	 */
	protected $is_validated = false;


	/**
	 * A comment or description of the email address. For example, "work" or "home."
	 *
	 * @var string
	 * @Column(name="comment", type="text")
	 */
	protected $comment = '';


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;



	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = new \DateTime();
	}
}