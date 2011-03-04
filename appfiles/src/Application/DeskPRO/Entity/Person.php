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

use \Application\DeskPRO\App;
use \Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity\UsergroupPropertyPermission;
use \Application\DeskPRO\Entity;

/**
 * A "person" is a record in the database that stores information about a person.
 * Every person is capable of logging in, though it may be the case that many wont (ie they are just contact cards).
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Person")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="people")
 */
class Person extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The users profile picture
	 *
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:OneToOne(targetEntity="Blob")
	 * @orm:JoinColumn(name="picture_blob_id", referencedColumnName="id")
	 */
	protected $picture_blob = null;

	/**
	 * The URL to the users gravatar if any
	 *
	 * @var string
	 * @orm:Column(name="gravatar_url", type="text")
	 */
	protected $gravatar_url = '';

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
	 * @orm:Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = 0;

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
	 * The locale associate with the user.
	 *
	 * @var \Application\DeskPRO\Entity\Locale
	 * @orm:ManyToOne(targetEntity="Locale")
	 * @orm:JoinColumn(name="locale_id", referencedColumnName="id")
	 */
	protected $locale = null;

	/**
	 * @var int
	 * @orm:Column(name="organization_id", type="integer", nullable=true)
	 */
	protected $organization_id = null;

	/**
	 * The users organization
	 *
	 * @var \Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization")
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
	protected $timezone = 'UTC';

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
	 * The primary email address used by this account
	 *
	 * @var \Application\DeskPRO\Entity\PersonEmail
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
	 * @orm:OneToMany(targetEntity="LabelPerson", mappedBy="person", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @orm:OneToMany(targetEntity="CustomDataPerson", mappedBy="person", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonContactData", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $contact_data;

	/**
	 * Usergroups the user belongs to
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Usergroup")
	 * @orm:JoinTable(name="person2usergroups",
	 *     joinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")},
     *     inverseJoinColumns={@orm:JoinColumn(name="usergroup_id", referencedColumnName="id")}
     * )
	 */
	protected $usergroups;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PersonPref", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $preferences;

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
	protected $date_last_login = null;

	/**
	 * The last time the users gravatar (or other 3rd party image) was checked.
	 *
	 * @var \DateTime
	 * @orm:Column(name="date_picture_check", type="datetime", nullable=true)
	 */
	protected $date_picture_check = null;

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

	/**
	 * An array of name=>value for loaded preferences. These are not obejcts.
	 * @var array
	 */
	protected $_pref_values = array();

	/**
	 * AN array of names we've loaded. This is because values can be null if they
	 * dont exist, but we dont want to keep trying ot laod them every time they're requested.
	 * @var array
	 */
	protected $_pref_loaded = array();

	protected $_label_manager = null;

	protected $_helper_manager = null;

	public static function newRegularPerson()
	{
		$person = new self();

		$usergroup = App::getEntityRepository('DeskPRO:Usergroup')->find(App::getSetting('core.default_usergroup_id'));
		$person->addUsergroup($usergroup);

		return $person;
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->secret_string = Strings::random(40);
		$this->timezone = 'UTC';

		$this->salt = Strings::random(40);

		$this->emails              = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usergroups          = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usersource_assoc    = new \Doctrine\Common\Collections\ArrayCollection();
		$this->personscraper_assoc = new \Doctrine\Common\Collections\ArrayCollection();
		$this->contact_data        = new \Doctrine\Common\Collections\ArrayCollection();
		$this->custom_data         = new \Doctrine\Common\Collections\ArrayCollection();
		$this->preferences         = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getOrganizationId()
	{
		if ($this->organization) {
			return $this->organization['id'];
		} else {
			return 0;
		}
	}

	public function setOrganizationId($org_id)
	{
		if ($org_id) {
			$org = App::getEntityRepository('DeskPRO:Organization')->find($org_id);
			$this->organization = $org;
		} else {
			$this->organization = null;
		}
	}


	/**
	 * Add a new helper
	 *
	 * @param string $name Name of the helper class
	 */
	public function loadHelper($name, array $options = array())
	{
		$classname = 'Application\\DeskPRO\\People\\Helpers\\' . $name;

		if (!$this->getHelperManager()->hasHelper($name)) {
			$object = new $classname($this, $options);
			$this->getHelperManager()->addHelper($object);
		}
	}

	/**
	 * Get a registered helper
	 * @param string $name
	 * @return mixed
	 */
	public function getHelper($name)
	{
		return $this->getHelperManager()->getHelper($name);
	}


	protected function _onNotCallable($name, $arguments)
	{
		if ($this->_helper_manager) {
			$name_l = strtolower($name);
			if ($this->_helper_manager->isNameCallable($name_l)) {
				return $this->_helper_manager->callName($name_l, $arguments);
			}
		}

		return parent::_onNotCallable($name, $arguments);
	}


	public function getLocale()
	{
		if ($this->locale) {
			return $this->locale;
		}

		return App::getEntityRepository('DeskPRO:Locale')->find(App::getSetting('core.default_locale_id'));
	}

	public function getLocaleId()
	{
		return $this->getLocale()->getId();
	}

	public function getActualLocale()
	{
		return $this->locale;
	}

	public function getActualLocaleId()
	{
		if ($this->locale) {
			return $this->locale;
		}

		return 0;
	}

	public function setLocaleId($locale_id)
	{
		if ($locale_id) {
			$this->locale = App::getEntityRepository('DeskPRO:Locale')->find($locale);
		} else {
			$this->locale = null;
		}
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
	 * Add a preference value to this user.
	 *
	 * @param Entity\PersonPref $pref
	 */
	public function addPreference(PersonPref $pref)
	{
		$this->preferences->add($pref);
		$pref['person'] = $this;
	}



	/**
	 * Get the value of a preference as it's currently stored.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getPref($name)
	{
		if (!in_array($name, $this->_pref_loaded)) {
			$this->_pref_values[$name] = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId($name, $this->id);
		}

		if (isset($this->_pref_values[$name])) {
			return $this->_pref_values[$name];
		}

		return null;
	}



	/**
	 * Get an array of named preferences.
	 *
	 * @param string $names...
	 * @return array
	 */
	public function getNamedPrefs()
	{
		if (func_num_args() == 1) {
			$names = array();
			$names[] = func_get_arg(0);
		} else {
			$names = func_get_args();
		}

		// Filter out ones we already have
		$loaded = $this->_pref_loaded;
		$names_get = array_filter($names, function ($v) use ($loaded) {
			if (in_array($v, $loaded)) {
				return false;
			}
			return true;
		});

		if ($names_get) {
			$got = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId($names_get, $this->id);
			$this->_pref_values = array_merge($this->_pref_values, $got);
			$this->_pref_loaded = array_merge($this->_pref_loaded, array_keys($got));
		}

		$ret = array();
		foreach ($names as $n) {
			$ret[$n] = isset($this->_pref_values[$n]) ? $this->_pref_values[$n] : null;
		}

		return $ret;
	}



	/**
	 * Load a group of user prefs
	 * @param string $pref_group
	 * @return array
	 */
	public function loadPrefGroup($pref_group)
	{
		$group = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefgroupForPersonId($pref_group, $this->id, false);
		$this->_pref_values = array_merge(
			$this->_pref_values,
			$group
		);

		return $group;
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

		/** @var $db \Application\DeskPRO\DBAL\Connection */
		$db = App::getDb();
		$usergroup_ids = $this->getUsergroupIds();

		if (!$usergroup_ids) {
			$this->_effective_permissions = array();
			return;
		}

		$em = App::getOrm();
		$properties = $em->createQuery('
			SELECT DeskPRO:UsergroupProperty p
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
	 * Find an existing data record for a field id.
	 *
	 * @param int $field_id
	 * @return CustomDataPerson
	 */
	public function getCustomDataForField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data['field_id'] == $field_id) {
				return $data;
			}
		}

		return null;
	}



	/**
	 * Set custom field data for a particular field.
	 *
	 * @param int $field_id
	 * @param mixed $value
	 * @return mixed
	 */
	public function setCustomData($field_id, $value_type, $value)
	{
		$custom_data = $this->getCustomDataForField($field_id);
		$is_new = false;

		if (!$custom_data) {
			if ($value === null) return null;

			$is_new = true;

			$field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = new CustomDataPerson();
			$custom_data['field'] = $field;
		}

		if ($value === null) {
			$this['custom_data']->removeElement($custom_data);
			return null;
		}

		$custom_data[$value_type] = $value;

		if ($is_new) {
			$this->addCustomData($custom_data);
		}

		return $custom_data;
	}

	/**
	 * Add a custom data item to this ticket
	 *
	 * @param CustomDataTicket $data
	 */
	public function addCustomData(CustomDataPerson $data)
	{
		$this->custom_data->add($data);
		$data['person'] = $this;
	}



	/**
	 * Render a custom field
	 */
	public function renderCustomField($field_id, $context = 'html')
	{
		$f_def = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($field_id);

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, array($f_def));

		$value = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
		$rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

		return $rendered;
	}



	/**
	 * Get the primary email address, or null if this person has none.
	 *
	 * @return string
	 */
	public function getPrimaryEmailAddress()
	{
		if (!$this->primary_email) {
			return null;
		}

		return $this->primary_email['email'];
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
	 * Add a label
	 * @param Entity\LabelPerson $label
	 */
	public function addLabel(Entity\LabelPerson $label)
	{
		$label['person'] = $this;
		$this->labels->add($label);
	}



	public function getContactDataOfType($type)
	{
		if (strpos($type, 'Application\\DeskPRO\\') !== 0) {
			$type = \Application\DeskPRO\Form\ContactFieldHandler\AbstractContactFieldHandler::simpleNameToClassName($type);
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
	 * Set the picture blob
	 *
	 * @param Entity\Blob $blob
	 */
	public function setPictureBlob(Entity\Blob $blob = null)
	{
		$this->picture_blob = $blob;
	}



	/**
	 * Sets the gravatar URL
	 *
	 * @param string $url
	 */
	public function setGravatarUrl($url)
	{
		$this->gravatar_url = $url;
	}



	/**
	 * Gets the URL to a picture for the person. Note that this will always return
	 * a path to an image, even if it's the default. If you need to check for the
	 * existance of an image, use hasPicture.
	 *
	 * @return null|string
	 */
	public function getPictureUrl($size = 80, $secure = null)
	{
		// Null means detect
		if ($secure === null AND App::isWebRequest()) {
			$request = App::getRequest();
			if ($request->isSecure()) {
				$secure = true;
			}
		}

		$url = false;
		if ($this->hasPicture()) {
			if ($this->picture_blob) {
				$url = App::get('router')->generate('serve_blob', array(
					'blob_auth_id' => $this->picture_blob->getAuthId(),
					's' => $size
				), true);

			} elseif (App::getSetting('core.use_gravatar') AND $this->gravatar_url) {
				$url = $this->gravatar_url;
				if ($size != 80) {
					$url .= '&s=' . $size;
				}
			}
		}

		if (!$url) {
			$url = App::get('router')->generate('serve_default_picture', array(
				's' => $size
			), true);
		}

		if ($secure) {
			$url = preg_replace('#^http:#', 'https:', $url);
		}

		return $url;
	}



	/**
	 * Does this user have a picture associated with their account?
	 *
	 * @return bool
	 */
	public function hasPicture($auto_check = true)
	{
		if ($this->picture_blob OR $this->gravatar_url) {
			return true;
		}

		// Try to auto-update gravatar
		if (!$this->gravatar_url AND $this->primary_email AND App::getSetting('core.use_gravatar')) {
			if (!$this->gravatar_url) {
				$url = null;

				$do_autocheck = false;
				if ($auto_check && $this->date_picture_check < date_create('-2 days')) {
					$do_autocheck = true;
				}

				if (App::getSetting('core.use_default_gravatar') OR ($do_autocheck AND $this->primary_email->hasGravatar())) {
					$url = $this->primary_email->getGravatarUrl();
				}

				if ($url) {
					$this->setGravatarUrl($url);
					App::getOrm()->persist($this);
					App::getOrm()->flush();
				}
			}

			if ($this->gravatar_url) {
				return true;
			}
		}

		return false;
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



	/** @orm:PrePersist */
	public function _prePersist()
	{
		if (!$this->date_created) {
			$this->date_created = new \DateTime();
		}
	}



	/**
	 * Get a Person ID from some parameter that might be a person, already a
	 * person ID, or some object that knows about a person ID.
	 *
	 * @param mixed $person
	 * @return int
	 */
	public static function smartPersonId($person)
	{
		if (is_int($person)) {
			return $person;
		} elseif (ctype_digit($person)) {
			return (int)$person;
		} elseif (\is_object($person)) {
			if ($person instanceof Person) {
				return (int)$person['id'];
			}
		} elseif (isset($person['person_id'])) {
			return (int)$person['person_id'];
		}

		return null;
	}



	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelPerson');
		}

		return $this->_label_manager;
	}


	public function getHelperManager()
	{
		if ($this->_helper_manager === null) {
			$this->_helper_manager = new \Orb\Helper\HelperManager();
		}

		return $this->_helper_manager;
	}
}
