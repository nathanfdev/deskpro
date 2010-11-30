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

use \DeskPRO\App;
use \DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\CoreBundle\Entity\UsergroupPropertyPermission;
use \Application\CoreBundle\Entity\PersonFieldDada;

/**
 * A "person" is a record in the database that stores information about a person.
 * Every person is capable of logging in, though it may be the case that many wont (ie they are just contact cards).
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="people")
 */
class Person extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Is this person a contact (someone we care about seeing)?
	 *
	 * @var bool
	 * @orm:Column(name="is_contact", type="boolean")
	 */
	protected $is_contact = true;

	/**
	 * Is this person a user (someone with login credentials)?
	 * 
	 * @var bool
	 * @orm:Column(name="is_user", type="boolean")
	 */
	protected $is_user = false;

	/**
	 * Is this person a tech?
	 *
	 * @var bool
	 * @orm:Column(name="is_tech", type="boolean")
	 */
	protected $is_tech = false;
	
	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @orm:Column(name="name", type="text")
	 */
	protected $name = '';

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @orm:Column(name="first_name", type="text", nullable=true)
	 */
	protected $first_name = '';

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @orm:Column(name="last_name", type="text", nullable=true)
	 */
	protected $last_name = '';

	/**
	 * A secret string used in various hashing or encryption schemes.
	 *
	 * @var string
	 * @orm:Column(name="secret_string", type="string", length=40)
	 */
	protected $secret_string;

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
	 * @orm:OneToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language = null;

	/**
	 * @var int
	 * @orm:Column(name="organization_id", type="integer", nullable=true)
	 */
	protected $organization_id = null;

	/**
	 * The users organization
	 *
	 * @var \Application\CoreBundle\Entity\Organization
	 * @orm:OneToOne(targetEntity="Organization")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id")
	 */
	protected $organization = null;

	/**
	 * The persons position at the organization
	 *
	 * @var string
	 * @orm:Column(name="organization_position", type="string", length=100)
	 */
	protected $organization_position = '';

	/**
	 * The timezone associated with this user.
	 *
	 * @var string
	 * @orm:Column(name="timezome", type="string", length=50)
	 */
	protected $timezone;

	/**
	 * Every person has a local login capability with this password and using
	 * an email address.
	 *
	 * @var string
	 * @orm:Column(name="password", type="string", length=40, nullable=true)
	 */
	protected $password = null;

	/**
	 * A salt used to hash the password with.
	 *
	 * @var string
	 * @orm:Column(name="salt", type="string", length=40)
	 */
	protected $salt;

	/**
	 * The primary email id.
	 *
	 * @var int
	 * @orm:Column(name="primary_email_id", type="integer", nullable=true)
	 */
	protected $primary_email_id = null;

	/**
	 * The primary email address used by this account
	 *
	 * @var \Application\CoreBundle\Entity\PersonEmail
	 * @orm:OneToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @orm:JoinColumn(name="primary_email_id", referencedColumnName="id")
	 */
	protected $primary_email;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonEmail", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $emails;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonContactData", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $contact_data;

	/**
	 * Usergroups the user belongs to
	 * 
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Usergroup")
	 * @orm:JoinTable(name="person2usergroups",
	 *     joinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")},
     *     inverseJoinColumns={@orm:JoinColumn(name="usergroup_id", referencedColumnName="id")}
     * )
	 */
	protected $usergroups;

	/**
	 * Usersource associations
	 *
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonUsersourceAssoc", mappedBy="person")
	 */
	protected $usersource_assoc;

	/**
	 * Person scraper associations
	 *
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonScraperAssoc", mappedBy="person")
	 */
	protected $personscraper_assoc;

	/**
	 * The date the user was inserted into the system
	 * 
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The last time the user logged in
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_last_login", type="datetime", nullable=true)
	 */
	protected $date_last_login;

	/**
	 * If we have set a password for this user, then the plaintext version will be set here.
	 *
	 * @var string
	 */
	protected $_set_plain_password = null;

	/**
	 * An array of effective permissions for this user based on usergroups.
	 *
	 * @var array
	 */
	protected $_effective_permissions = null;

	/**
	 * An array of usergroupids this user belongs to
	 *
	 * @var array
	 */
	protected $_usergroup_ids = null;



	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->secret_string = Strings::random(40);
		$this->timezone = 'UTC';

		$this->salt = Strings::random(40);

		$this->emails = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usergroups = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usersource_assoc = new \Doctrine\Common\Collections\ArrayCollection();
		$this->personscraper_assoc = new \Doctrine\Common\Collections\ArrayCollection();
		$this->contact_data = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * Get a string display name we can call this person.
	 * @return string
	 */
	public function getDisplayName()
	{
		if ($this['first_name'] AND $this['last_name']) {
			return $this['first_name'] . ' ' . $this['last_name'];
		} elseif ($this['name']) {
			return $this['name'];
		} elseif ($this['last_name']) {
			return $this['last_name'];
		} elseif ($this['primary_email']) {
			return $this['primary_email']['email'];
		} else {
			return 'ID-' . $this['id'];
		}
	}
	


	/**
	 * Check to see if a password is the same one we have on record. Used with local auth.
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
	 * Sets the hashed form of the password for this user. Used with local auth.
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
	 * @param  string $plain_password The password to hash
	 * @return string
	 */
	public function hashPassword($plain_password)
	{
		return sha1($this->salt . $plain_password);
	}



	/**
	 * Get the value of a permission
	 *
	 * @param string $name The permission name
	 * @return mixed
	 */
	public function getPermission($name)
	{
		$this->_loadEffectivePermissions();

		if (!isset($this->_effective_permissions[$name])) {
			return null;
		}

		return $this->_effective_permissions[$name];
	}

	protected function _loadEffectivePermissions()
	{
		if ($this->_effective_permissions !== null) return;

		if (!$this->id) {
			$this->_effective_permissions = array();
			return;
		}

		/** @var $db \DeskPRO\DBAL\Connection */
		$db = App::getDb();
		$usergroup_ids = $this->getUsergroupIds();

		if (!$usergroup_ids) {
			$this->_effective_permissions = array();
			return;
		}

		$em = App::getOrm();
		$properties = $em->createQuery('
			SELECT CoreBundle:UsergroupProperty p
			WHERE usergroup_id IN ?1 AND property_type = ?2
		')->setParameter(1, $usergroup_ids)
			->setParameter(2, UsergroupPropertyPermission::PROPERTY_TYPE)
			->getResult();

		$this->_effective_permissions = UsergroupPropertyPermission::coalescePermissionProperties($properties);
	}

	

	/**
	 * Get an array of usergroup ID's this user belongs to.
	 *
	 * @return array
	 */
	public function getUsergroupIds()
	{
		if ($this->_usergroup_ids !== null) return $this->_usergroup_ids;

		// If we have the usergroups collection, we can just use that
		if (ORM_Util::isCollectionInitialized($this->usergroups)) {

			$this->_usergroup_ids = array();
			foreach ($this->usergroups as $ug) {
				$this->_usergroup_ids[] = $ug['id'];
			}

		// Otherwise we'll try and just fetch simple values
		// with a quick query
		} else {
			$this->_usergroup_ids = $db->fetchAllCol("
				SELECT usergroup_id
				FROM user2usergroups
				WHERE person_id = {$this->id}
			");
		}

		return $this->_usergroup_ids;
	}


	/**
	 * Add contact data
	 *
	 * @param PersonEmail $email
	 */
	public function addContactData(PersonContactData $contact_data)
	{
		$em = App::getOrm();

		$this['contact_data']->add($contact_data);

		$contact_data['person'] = $this;
		$em->persist($contact_data);
	}



	/**
	 * Add an email address
	 *
	 * @param PersonEmail $email
	 */
	public function addEmailAddress(PersonEmail $email)
	{
		$em = App::getOrm();

		if ($this['emails']->count() < 1) {
			$this['primary_email'] = $email;
		}
		$this['emails']->add($email);

		$email['person'] = $this;
		$em->persist($email);
	}


	
	/**
	 * Remove an email address from this user.
	 * 
	 * Note: This should be run within a transaction if you want to :)
	 *
	 * The old PersonEmail will be returned.
	 *
	 * @param int $email_id
	 * @return PersonEmail
	 */
	public function removeEmailAddressId($email_id)
	{
		$em = App::getOrm();

		$the_email = null;
		foreach ($this->emails as $index => $email) {
			if ($email['id'] == $email_id) {
				$this->emails->remove($index);
				$em->remove($email);
				return $email;
			}
		}

		return null;
	}


	public function getEmailId($email_id)
	{
		foreach ($this->emails as $index => $email) {
			if ($email['id'] == $email_id) {
				return $email;
			}
		}
	}



	/**
	 * Add a new usergroup
	 * 
	 * @param Usergroup $usergroup
	 */
	public function addUsergroup(Usergroup $usergroup)
	{
		$this['usergroups']->add($usergroup);
	}



	/**
	 * Add a new field
	 * @param PersonField $field
	 */
	public function addFieldData(PersonFieldData $field)
	{
		$this->field_data->add($field);
		$field['person'] = $this;
	}



	/**
	 * Gets field data for 'top' fields, that is, don't return fields that are children.
	 * Most of the time we work with those values strictly through the parent field.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return array();
		$array = array();

		foreach ($this->field_data as $f) {
			if ($f['parent_id']) continue;

			$array[$f['person_field_id']] = $f;
		}

		return $array;
	}


	
	/**
	 * Get fielddata for a specific field. Returns null if no data for a field exists.
	 *
	 * @param $field_id
	 * @return PersonFieldData
	 */
	public function getField($field_id)
	{
		foreach ($this->field_data as $f) {
			if ($f['person_field_id'] == $field_id AND !$f['parent_id']) {
				return $f;
			}
		}

		return null;
	}


	public function getContactDataOfType($type)
	{
		if (strpos($type, 'DeskPRO\\') !== 0) {
			$type = \DeskPRO\Form\ContactFieldHandler\AbstractContactFieldHandler::simpleNameToClassName($type);
		}

		$ret = array();
		foreach ($this->contact_data as $contact_data) {
			if ($contact_data['handler_class'] == $type) {
				$ret[] = $contact_data;
			}
		}

		return $ret;
	}

	public function getIms()
	{
		return $this->getContactDataOfType('instant_message');
	}

	public function getAddresses()
	{
		return $this->getContactDataOfType('address');
	}

	public function getPhones()
	{
		return $this->getContactDataOfType('phone');
	}



	

	/**
	 * Gets the rendered values of fields, indexed by
	 *
	 * @array
	 */
	public function renderFieldValues($context = 'html')
	{
		$array = array();

		foreach ($this->field_data as $f) {
			if ($f['parent_id']) continue;
			$array[$f['person_field_id']] = $f->renderContext($context);
		}

		return $array;
	}

	

	/**
	 * Get a single value
	 *
	 * @param int $person_field_id
	 * @param string $context
	 * @return string|null Null if no field value
	 */
	public function renderSingleFieldValue($person_field_id, $context = 'html')
	{
		foreach ($this->field_data as $f) {
			if ($f['parent_id']) continue;
			if ($f['person_field_id'] == $person_field_id) {
				return $f->renderContext($context);
			}
		}

		return null;
	}

	

	/**
	 * Set this persons organization and position.
	 *
	 * @param Organization $org
	 * @param string $position 
	 */
	public function setOrganization(Organization $org, $position = '')
	{
		$this->organization = $org;
		$this->organization_position = $position;
	}

	
	public function __toString()
	{
		return $this->getDisplayName();
	}


	
	public function getKeys()
	{
		$keys = parent::getKeys();
		$keys[] = 'display_name';

		return $keys;
	}



	/**
	 * Set the last time this usersource was used.
	 *
	 * @param DateTime $time The time to set, or null to set now
	 */
	public function setLastLoginAt(\DateTime $time = null)
	{
		if (!$time) $time = new \DateTime();

		$this->date_last_login = $time;
	}



	/** @PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}
}
