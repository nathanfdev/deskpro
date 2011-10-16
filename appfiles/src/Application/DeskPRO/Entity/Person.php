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
use Application\DeskPRO\ORM\Util\Util as ORM_Util;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\Entity;

/**
 * A "person" is a record in the database that stores information about a person.
 * Every person is capable of logging in, though it may be the case that many wont (ie they are just contact cards).
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Person")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="people")
 */
class Person extends \Application\DeskPRO\Domain\DomainObject
{
	const CREATED_WEB_PERSON = 'web.person';
	const CREATED_WEB_AGENT = 'web.agent';
	const CREATED_WEB_USERSOURCE = 'web.usersource';
	const CREATED_GATEWAT_PERSON = 'gateway.person';

	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * The users profile picture
	 *
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\OneToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="picture_blob_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $picture_blob = null;

	/**
	 * The URL to the users gravatar if any
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="gravatar_url", type="text")
	 */
	protected $gravatar_url = '';

	/**
	 * Is this person a contact (someone we care about seeing)?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_contact", type="boolean")
	 */
	protected $is_contact = true;

	/**
	 * Is this person a user (someone with login credentials)?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_user", type="boolean")
	 */
	protected $is_user = false;

	/**
	 * Is this person a tech?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = 0;

	/**
	 * Autoresponds
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_autoresponder", type="boolean")
	 */
	protected $is_autoresponder = 0;

	/**
	 * Has this user ever confirmed themselves via email?
	 * Individual email addresses must be confirmed as well, but this
	 * is an account-wide flag that says the user is at least real.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_confirmed", type="boolean")
	 */
	protected $is_confirmed = false;

	/**
	 * Has this user ever confirmed themselves via email?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent_confirmed", type="boolean")
	 */
	protected $is_agent_confirmed = false;

	/**
	 * The user importance, 0-5
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="importance", type="smallint")
	 */
	protected $importance = 0;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="creation_system", type="string", length=20)
	 */
	protected $creation_system = 'web.person';

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="text")
	 */
	protected $name = '';

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="first_name", type="text", nullable=true)
	 */
	protected $first_name = '';

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="last_name", type="text", nullable=true)
	 */
	protected $last_name = '';

	/**
	 * The summary field as filled in by agents
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="summary", type="text")
	 */
	protected $summary = '';


	/**
	 * A secret string used in various hashing or encryption schemes.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="secret_string", type="string", length=40)
	 */
	protected $secret_string;

	/**
	 * The language associate with the user.
	 *
	 * @var \Application\DeskPRO\Entity\Language
	 * @ORM_Mapping\ManyToOne(targetEntity="Language", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="language_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $language = null;

	/**
	 * The users organization
	 *
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $organization = null;

	/**
	 * The persons position at the organization
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="organization_position", type="string", length=100)
	 */
	protected $organization_position = '';

	/**
	 * The timezone associated with this user.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="timezone", type="string", length=50)
	 */
	protected $timezone = 'UTC';

	/**
	 * Every person has a local login capability with this password and using
	 * an email address.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="password", type="string", length=40, nullable=true)
	 */
	protected $password = null;

	/**
	 * A salt used to hash the password with.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="salt", type="string", length=40)
	 */
	protected $salt;

	/**
	 * The primary email address used by this account
	 *
	 * @var \Application\DeskPRO\Entity\PersonEmail
	 * @ORM_Mapping\OneToOne(targetEntity="PersonEmail", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="primary_email_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $primary_email;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="PersonEmail", mappedBy="person", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $emails;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelPerson", mappedBy="person", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="CustomDataPerson", mappedBy="person", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $custom_data;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="PersonContactData", mappedBy="person", cascade={"persist", "remove", "merge"}, indexBy="id")
	 */
	protected $contact_data;

	/**
	 * Usergroups the user belongs to
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Usergroup", fetch="EAGER", indexBy="id")
	 * @ORM_Mapping\JoinTable(name="person2usergroups",
	 *     joinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")}
     * )
	 */
	protected $usergroups;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="PersonPref", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $preferences;

	/**
	 * Usersource associations
	 *
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="PersonUsersourceAssoc", mappedBy="person", indexBy="id")
	 */
	protected $usersource_assoc;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="TwitterAccount", mappedBy="persons")
	 */
	protected $twitter_accounts;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusNote", mappedBy="person")
	 */
	protected $twitter_status_notes;

	/**
	 * The date the user was inserted into the system
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The last time the user logged in
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_last_login", type="datetime", nullable=true)
	 */
	protected $date_last_login = null;

	/**
	 * The last time the users gravatar (or other 3rd party image) was checked.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_picture_check", type="datetime", nullable=true)
	 */
	protected $date_picture_check = null;

	/**
	 * The tasks created by this user.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Task", mappedBy="creator", cascade={"persist", "remove", "merge"})
	 */
	protected $created_tasks;

	/**
	 * The tasks assigned to this user.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Task", mappedBy="assigned_agent", cascade={"persist", "remove", "merge"})
	 */
	protected $assigned_tasks;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TaskComment", mappedBy="person")
	 */
	protected $task_comments;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="TaskAssociatedPerson", mappedBy="person")
	 */
	protected $task_associations;


	/**
	 * If we have set a password for this user, then the plaintext version will be set here.
	 * @var string
	 */
	protected $_set_plain_password = null;

	/**
	 * An array of usergroupids this user belongs to
	 * @var array
	 */
	protected $_twitter_account_ids = null;

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

	/**
	 * Label manager for adding/removing labels
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

	/**
	 * Helper manager for auto-loading functionality onto this object
	 * @var \Orb\Helper\HelperManager
	 */
	protected $_helper_manager = null;

	/**
	 * The permissions manager helper once its loaded
	 * @var \Application\DeskPRO\People\Helpers\PermissionsManager
	 */
	protected $_permissions_manager = null;

	/**
	 * True if this is a new record.
	 * @var bool
	 */
	protected $_is_new_person = false;

	/**
	 * @var \Application\DeskPRO\People\PersonChangeTracker
	 */
	protected $_person_logger = null;

        /**
	 * The deals created by this user.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Deal", mappedBy="person", cascade={"persist", "remove", "merge"})
	 */
	protected $deals;

	/**
	 * The deals assigned to this user.
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="Deal", mappedBy="assigned_agent", cascade={"persist", "remove", "merge"})
	 */
	protected $assigned_deals;



	/**
	 * A "contact person" is simply a person record. They have no login credentials, they are not
	 * a full user.
	 *
	 * (This is really just the same as calling the constructor yourself, but we may need to
	 * change this functionality in the future so its a method).
	 *
	 * @static
	 * @return Person
	 */
	public static function newContactPerson(array $info = null)
	{
		$person = new self();

		if ($info) {
			$person->fromArray($info);
		}

		return $person;
	}

	/**
	 * A regular person is a person who can log in. They are a full user.
	 *
	 * @static
	 * @return Person
	 */
	public static function newRegularPerson()
	{
		$person = new self();
		$person['is_user'] = true;
		return $person;
	}

	public function __construct()
	{
		$this->_is_new_person = true;

		$this->date_created     = new \DateTime();
		$this->secret_string    = Strings::random(40);
		$this->timezone         = 'UTC';
		$this->salt             = Strings::random(40);

		$this->emails                 = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usergroups             = new \Doctrine\Common\Collections\ArrayCollection();
		$this->usersource_assoc       = new \Doctrine\Common\Collections\ArrayCollection();
		$this->personscraper_assoc    = new \Doctrine\Common\Collections\ArrayCollection();
		$this->contact_data           = new \Doctrine\Common\Collections\ArrayCollection();
		$this->custom_data            = new \Doctrine\Common\Collections\ArrayCollection();
		$this->preferences            = new \Doctrine\Common\Collections\ArrayCollection();
		$this->twitter_accounts       = new \Doctrine\Common\Collections\ArrayCollection();
		$this->twitter_status_notes   = new \Doctrine\Common\Collections\ArrayCollection();
		$this->created_tasks          = new \Doctrine\Common\Collections\ArrayCollection();
		$this->assigned_tasks         = new \Doctrine\Common\Collections\ArrayCollection();
		$this->task_comments          = new \Doctrine\Common\Collections\ArrayCollection();
		$this->task_associations      = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels                 = new \Doctrine\Common\Collections\ArrayCollection();
                $this->deals      = new \Doctrine\Common\Collections\ArrayCollection();
		$this->assigned_deals                 = new \Doctrine\Common\Collections\ArrayCollection();

		$this->_initPersonLogger();
		$this->_person_logger->recordExtra('person_created', true);
	}

	public function isGuest()
	{
		return false;
	}

	/**
	 * @ORM_Mapping\PostLoad
	 */
	public function _initPersonLogger()
	{
		$person_logger = new \Application\DeskPRO\People\PersonChangeTracker($this);
		$this->_person_logger = $person_logger;
		$this->addPropertyChangedListener($person_logger);
	}

	public function hasPerm($name)
	{
		return $this->getHelper('PermissionsManager')->hasPerm($name);
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

	public function __get($name)
	{
		if ($this->_helper_manager) {
			$name_l = strtolower($name);
			if ($this->_helper_manager->isNameCallable($name_l)) {
				return $this->_helper_manager->callName($name_l, array());
			}
		}

		return parent::__get($name);
	}

	public function __isset($name)
	{
		if ($this->_helper_manager) {
			$name_l = strtolower($name);
			if ($this->_helper_manager->isNameCallable($name_l)) {
				return true;
			}
		}

		return parent::__isset($name);
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
		} elseif ($this['first_name']) {
			return $this['first_name'];
		} elseif ($this['primary_email']) {

			// try to get a nice name from the email address
			$email = $this['primary_email']['email'];
			list ($name,) = explode('@', $email, 2);

			$name = str_replace('_', ' ', $name);
			$name = str_replace('.', ' ', $name);
			$name = preg_replace('#[ ]{2,}#', ' ', $name); //consec spaces to single space

			$name = ucfirst($name);

			return $name;
		} else {
			return 'ID-' . $this['id'];
		}
	}



	/**
	 * Gets this persons name and their primary email address
	 *
	 * @return string
	 */
	public function getDisplayContact()
	{
		$display = $this->getDisplayName();
		if ($this->getPrimaryEmailAddress()) {
			$display .= " ({$this->getPrimaryEmailAddress()})";
		}

		return $display;
	}


	/**
	 * Gets this persons name and primary email address. If their name is
	 * long, we'll try to initialize it or try other ways to shorten the name
	 * to the specified number of characters.
	 *
	 * @return string
	 */
	public function getDisplayContactShort($max_len = 40)
	{
		/*
		 * n = name
		 * e = email
		 * fi = first initial
		 * li = last initial
		 * fn = first name
		 * ln = last name
		 */
		$try = array(
			array('fn', ' ', 'ln', ' ', '(e)'),
			array('fi', ' ', 'ln', ' ', '(e)'),
			array('fn', ' ', 'li', ' ', '(e)'),
			array('n', ' ', '(e)'),
			array('fn', ' ', 'ln'),
			array('fn', ' ', 'li'),
			array('fi', 'ln'),
			array('fi', 'li', ' ', '(e)'),
			array('fi', 'li'),
			array('e'),
			array('n')
		);

		$shortest = null;
		$shortest_len = null;

		foreach ($try as $k => $elements) {

			$display = array();

			foreach ($elements as $el) {
				switch ($el) {
					case 'n':
						if (!$this->name) continue 2;
						$display[] = $this->name;
						break;

					case 'fi':
						if (!$this->first_name) continue 2;
						$display[] = $this->first_name[0];
						break;

					case 'fn':
						if (!$this->first_name) continue 2;
						$display[] = $this->first_name;
						break;

					case 'li':
						if (!$this->last_name) continue 2;
						$display[] = $this->last_name[0];
						break;

					case 'ln':
						if (!$this->last_name) continue 2;
						$display[] = $this->last_name;
						break;

					case '(e)':
					case 'e':
						if (!$this->getPrimaryEmailAddress()) continue 2;

						if ($el == '(e)') {
							$display[] = "({$this->getPrimaryEmailAddress()})";
						} else {
							$display[] = $this->getPrimaryEmailAddress();
						}
						break;

					default:
						$display[] = $el;
						break;
				}
			}

			$display = implode('', $display);
			$len = strlen($display);

			if ($len <= $max_len) {
				return $display;
			}

			if ($shortest === null OR $len < $shortest_len) {
				$shortest = $display;
				$shortest_len = $len;
			}
		}

		// If we got down here, we have no choice but to show
		// whatever we have
		if ($shortest !== null) {
			return $shortest;
		} else {
			return $this->getDisplayName();
		}
	}


	/**
	 * Set the importance of this user
	 *
	 * @param int $importance
	 */
	public function setImportance($importance)
	{
		$old = $this->importance;
		$this->importance = Numbers::bound($importance, 0, 5);
		$this->_onPropertyChanged('importance', $old, $this->importance);
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
	 * Set the value of a preference. Will update if it exists, or create a new one
	 * if it doesnt.
	 *
	 * @param  $pref
	 * @param  $value
	 * @return PersonPref
	 */
	public function setPreference($pref_name, $value)
	{
		$pref = App::getEntityRepository('DeskPRO:PersonPref')->getForPerson($pref_name, $this);
		if (!$pref) {
			$pref = new PersonPref();
			$pref['name'] = $pref_name;
			$this->addPreference($pref);
		}

		$pref['value'] = $value;
		$this->_pref_values[$pref_name] = $value;

		return $pref;
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
	 * Get the real language. This might be null if there is no preference for the user.
	 *
	 * @return \Application\DeskPRO\Entity\Language|null
	 */
	public function getRealLanguage()
	{
		return $this->language;
	}


	/**
	 * Get the users language
	 *
	 * @return \Application\DeskPRO\Entity\Language
	 */
	public function getLanguage()
	{
		if ($this->language) {
			return $this->language;
		}

		return App::getEntityRepository('DeskPRO:Language')->getDefault();
	}



	/**
	 * Get the locale string
	 *
	 * Example: en_US
	 *
	 * @return string
	 */
	public function getLocale()
	{
		$lang = $this->getLanguage();
		return $lang->getLocale();
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
	 * Get the value of a usergroup permission
	 *
	 * @param string $name The permission name
	 * @return mixed
	 */
	public function getPermission($name)
	{
		return $this->getPermissionsManager()->Usergroups->getPermission($name);
	}



	/**
	 * Get an array of usergroup ID's this user belongs to.
	 *
	 * @return array
	 */
	public function getUsergroupIds()
	{
		$this->getPermissionsManager()->getUsergroupIds();
	}



	/**
	 * Get an array of twitter account ID's this user belongs to.
	 *
	 * @return array
	 */
	public function getTwitterAccountIds()
	{
		if ($this->_twitter_account_ids !== null) {
			return $this->_twitter_account_ids;
		}

		// If we have the usergroups collection, we can just use that
		if (ORM_Util::isCollectionInitialized($this->twitter_accounts)) {
			$this->_twitter_account_ids = array();
			foreach ($this->twitter_accounts as $ta) {
				$this->_twitter_account_ids[] = $ta->getId();
			}
		} else {
			$this->_twitter_account_ids = App::getDb()->fetchAllCol("
				SELECT account_id
				FROM twitter_accounts_person
				WHERE person_id = {$this->id}
			");
		}

		return $this->_twitter_account_ids;
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
	 *
	 * !depreciated
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
	 * Check if this ticket has a custom field.
	 *
	 * @param $field_id
	 * @return bool
	 */
	public function hasCustomField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data->field['id'] == $field_id) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Gets a display array for a specific field
	 * @param $field_id
	 * @return array|mixed|null
	 */
	public function getCustomFieldDisplayArray($field_id)
	{
		$data = $this->getCustomDataForField($field_id);
		if (!$data) {
			return null;
		}

		$ticket_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array($data), $ticket_field_defs);

		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray(
			$ticket_field_defs,
			$ticket_data_structured
		);

		$custom_fields = array_pop($custom_fields);

		return $custom_fields;
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
	 * Alias for getPrimaryEmailAddress
	 *
	 * @return string
	 */
	public function getEmailAddress()
	{
		return $this->getPrimaryEmailAddress();
	}


	/**
	 * Get the primary email address ID
	 *
	 * @return int
	 */
	public function getPrimaryEmailId()
	{
		if (!$this->primary_email) {
			return 0;
		}

		return $this->primary_email['id'];
	}


	/**
	 * Sets the primray email address on the account
	 *
	 * @param $email_address
	 * @return void
	 */
	public function setEmail($email_address, $validated = false)
	{
		$email = new PersonEmail();
		$email['email'] = $email_address;

		if ($validated) {
			$email['is_validated'] = true;
		}

		$this->addEmailAddress($email);

		$this->primary_email = $email;
	}



	/**
	 * Get email addresses that are validated
	 *
	 * @return array
	 */
	public function getValidatedEmails()
	{
		$ret = array();

		foreach ($this->emails as $email) {
			if ($email['is_validated']) {
				$ret[] = $email;
			}
		}

		return $ret;
	}



	/**
	 * Add an email address
	 *
	 * @param PersonEmail $email
	 */
	public function addEmailAddress(PersonEmail $email)
	{
		if (!$this->primary_email && $this->emails->count() < 1) {
			$this->primary_email = $email;
		}
		$this->emails->add($email);

		$email->person = $this;

		return $email;
	}


	/**
	 * Adds an emaila ddress string. This is same as addEmailAddress except
	 * we take care of creating the PersonEmail object here.
	 *
	 * @param string $email
	 * @return PersonEmail
	 */
	public function addEmailAddressString($email)
	{
		$email_obj = new PersonEmail();
		$email_obj['email'] = $email;

		$this->addEmailAddress($email_obj);

		return $email_obj;
	}



	/**
	 * Remove an email address from this user.
	 *
	 * The old PersonEmail will be returned.
	 *
	 * If this is the primary email, the next validated email address will be made
	 * primary. If there's no validated, then the next email address. If there are none,
	 * then the primary email is made null.
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
				$the_email = $email;
				break;
			}
		}

		if ($the_email AND $this->primary_email['id'] == $the_email['id']) {
			$next_email = null;
			$next_valid_email = null;
			foreach ($this->emails as $index => $email) {
				if (!$next_email) $next_email = $email;
				if (!$next_valid_email AND $email['is_validated']) $next_valid_email = $email;

				if ($next_email AND $next_valid_email) break;
			}

			if ($next_valid_email) {
				$this->primary_email = $next_valid_email;
				$em->persist($this);
			} else if ($next_email) {
				$this->primary_email = $next_email;
				$em->persist($this);
			}
		}

		return $the_email;
	}


	public function getEmailId($email_id)
	{
		foreach ($this->emails as $index => $email) {
			if ($email['id'] == $email_id) {
				return $email;
			}
		}

		return null;
	}


	/**
	 * Get the email record for a specific address
	 *
	 * @return Email
	 */
	public function findEmailAddress($email_address)
	{
		$email_address = strtolower($email_address);

		foreach ($this->emails as $email) {
			if (strtolower($email['email']) == $email_address) {
				return $email;
			}
		}

		return null;
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
	 * @param \Application\DeskPRO\Entity\LabelPerson $label
	 */
	public function addLabel(LabelPerson $label)
	{
		$label['person'] = $this;
		$this->labels->add($label);
	}

	public function getUsergroupSetKey()
	{
		if ($this->usergroups instanceof \Doctrine\Common\Collections\Collection) {
			$this->usergroups = $this->usergroups->toArray();
		}

		return Usergroup::generateUsergroupSetKey($this->usergroups);
	}


	/**
	 * Set the picture blob
	 *
	 * @param \Application\DeskPRO\Entity\Blob $blob
	 */
	public function setPictureBlob(Blob $blob = null)
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
	 * Is this a newly created person?
	 *
	 * It's important to realize the context of when a person is "new." This simply means
	 * that THIS object is new (was created with a constructor). If the object is persisted,
	 * then the person gains an ID etc but is still "new".
	 *
	 * As soon as the EntityManager
	 * loses the map (eg. page is refreshed, command ends, or the EM is clear()ed), then
	 * the person is no longer considered new, because they will have been hydrated and the
	 * constructor not called.
	 *
	 * @return bool
	 */
	public function isNewPerson()
	{
		return $this->_is_new_person;
	}



	/**
	 * Set this persons organization and position.
	 *
	 * @param Organization $org
	 * @param string $position
	 */
	public function setOrganization(Organization $org = null, $position = '')
	{
		if (!$org) {
			$this->setModelField('organization', $org);
			return;
		}

		$old_o = $this->organization;
		$old_op = $this->organization_position;

		$this->organization = $org;
		$this->organization_position = $position;

		$this->_onPropertyChanged('organization', $old_o, $this->organization);
		$this->_onPropertyChanged('organization', $old_op, $this->organization_position);

		// Improve importance when adding the user to the org that
		// has a higher importance
		if ($this->organization['importance'] > $this->importance) {
			$this->setImportance($this->organization['importance']);
		}
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
	 * This will sync the names fields as best as we can. For example, if first/last
	 * is set but not name, automatically set name
	 *
	 * If name is set but not first and last, try to smart-set first/last by splitting up
	 * the name.
	 *
	 * @ORM_Mapping\prePersist
	 * @ORM_Mapping\preUpdate
	 * @return void
	 */
	public function smartSetName()
	{
		if ($this->name) {
			if (!$this->first_name AND !$this->last_name) {
				$m = null;
				if (preg_match('#^(?P<first_name>[A-Za-z]{3,})\s+(?P<last_name>[A-Za-z]{3,})$#', $this->name, $m)) {
					$this->first_name = $m['first_name'];
					$this->last_name = $m['last_name'];
				}
			}
		} else {
			if ($this->first_name AND $this->last_name) {
				$old_name = $this->name;
				$this->name = $this->first_name . ' ' . $this->last_name;
				$this->_onPropertyChanged('name', $old_name, $this->name);
			}
		}
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



	/** @ORM_Mapping\PrePersist */
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



	/**
	 * @return \Application\DeskPRO\People\Helpers\PermissionsManager
	 */
	public function getPermissionsManager()
	{
		if ($this->_permissions_manager === null) {
			$this->loadHelper('PermissionsManager');
			$this->_permissions_manager = $this->getHelper('PermissionsManager');
		}

		return $this->_permissions_manager;
	}



	public function getHelperManager()
	{
		if ($this->_helper_manager === null) {
			$this->_helper_manager = new \Orb\Helper\HelperManager();
		}

		return $this->_helper_manager;
	}

	public function getPersonLogger()
	{
		return $this->_person_logger;
	}



	/**
	 * @ORM_Mapping\PostUpdate
	 * @ORM_Mapping\PostPersist
	 */
	public function _savePersonLogs()
	{
		if ($this->_person_logger) {
			$this->_person_logger->done();
		}
	}

	public function getTimezone()
	{
		if (!$this->timezone) {
			return 'UTC';
		}

		return $this->timezone;
	}

	public function getDateTimezone()
	{
		return new \DateTimeZone($this->getTimezone());
	}

	public function getDateTime()
	{
		return new \DateTime("now", $this->getDateTimezone());
	}

	public function getTimezoneOffset($as_string = false)
	{
		$user_offset = $this->getDateTimezone()->getOffset(new \DateTime("now"));
		$user_offset /= 3600; //hours

		if ($as_string) {
			if ($user_offset >= 0) {
				$user_offset = "+$user_offset";
			} else {
				$user_offset = "$user_offset";
			}
		}

		return $user_offset;
	}
}
