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
 * A "user" is a person in the database who can log in and has access to the interfaces.
 *
 * @Entity
 * @Table(name="users")
 */
class User extends Entity
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * @var Profile
	 * @OneToOne(targetEntity="Profile", mappedBy="user")
	 * @JoinColumn(name="profile_id", referencedColumnName="id")
	 */
	protected $profile;


	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection();
	 * @ManyToMany(targetEntity="Usergroup", inversedBy="users")
	 * @JoinTable(name="user2usergroups",
	 *     joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="usergroup_id", referencedColumnName="id")}
     * )
	 */
	protected $usergroups;


	/**
	 * Every user has a local login capability with this password. Null means there is no local auth.
	 *
	 * @var string
	 * @Column(name="password", type="string", length=255, nullable=true, default=null)
	 */
	protected $password = null;


	/**
	 * Which hashing algoirthm is used for storing the password.
	 *
	 * @var string
	 * @Column(name="password_algo", type="string", length=15, default="sha1")
	 */
	protected $password_algo = 'sha1';



	/**
	 * A salt used to hash the password with.
	 *
	 * @var string
	 * @Column(name="salt", type="string", length=40)
	 */
	protected $salt;


	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * The active, coalesced permissions that apply to this user after all usergroups
	 * are processed.
	 * @var array
	 */
	protected $_active_perms = null;


	/**
	 * If we have set a password for this user, then the plaintext version will be set here.
	 * @var string
	 */
	protected $_set_plain_password = null;



	public function __construct()
	{
		$this->salt = Strings::random(40);
		$this->usergroups = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * Check to see if a password is the same one we have on record.
	 *
	 * @param  $plain_password
	 * @return bool
	 */
	public function checkPassword($plain_password)
	{
		$hash = $this->hashPassword($plain_password);

		return ($hash == $this->password);
	}



	/**
	 * Sets the hashed form of the password for this user.
	 *
	 * @param  string $plain_password The password to set
	 * @return string
	 */
	public function setPassword($plain_password)
	{
		$hash = $this->hashPassword($plain_password);

		$this->password = $hash;
		$this->_set_plain_password = $plain_password;

		return $this->password;
	}



	/**
	 * If you have set a password, the plaintext version will be returned.
	 * Otherwise, null is returned.
	 *
	 * @return string
	 */
	public function getPlaintextPassword()
	{
		return $this->_set_plain_password;
	}



	/**
	 * Create a new password hash using the salt and algorithm used with this user.
	 *
	 * @throws DomainException
	 * @param  string $plain_password The password to hash
	 * @return string
	 */
	public function hashPassword($plain_password)
	{
		$hash = null;
		switch ($this->password_algo) {
			case 'sha1':
				$hash = sha1($this->salt . $plain_password);
				break;
			case 'plaintext':
				$hash = substr($plain_password, 0, 255);
				break;
			default:
				throw new DomainException('Unknown hashing algorithm: ' . $this->password_algo);
				break;
		}

		return $hash;
	}



	/**
	 * Checks to see if this user has a specific permission.
	 *
	 * @param  string $name The name of the permission
	 * @return bool
	 */
	public function checkPermission($name)
	{
		return (isset($this->_active_perms[$name]) AND $this->_active_perms[$name]);
	}



	/**
	 * Initializes the active permissions array.
	 *
	 * @see checkPermission
	 * @see $_active_perms
	 * @return void
	 */
	protected function _initActivePerms()
	{
		if ($this->_active_perms !== null) return;

		foreach ($this->usergroups as $group) {
			$perms = $group->getPermissionsArray();
			foreach ($perms as $name => $yesno) {
				if (!isset($this->_active_perms[$name]) OR !$this->_active_perms[$name]) {
					$this->_active_perms[$name] = (bool)$yesno;
				}
			}
		}
	}



	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = new \DateTime();
	}
}