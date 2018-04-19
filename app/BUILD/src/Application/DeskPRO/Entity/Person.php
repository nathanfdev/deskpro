<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\EventListener\PersonChangeLogListener;
use Application\DeskPRO\Entity\Labels\Label;
use Application\DeskPRO\Entity\Labels\LabelsOwner;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\People\PasswordPolicyValidator;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\EventListener\Person\PersonOnboardingListener;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use JMS\Serializer\Annotation as JMS;
use Orb\Data\FreeEmailProviders;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * A "person" is a record in the database that stores information about a person.
 * Every person is capable of logging in, though it may be the case that many wont (ie they are just contact cards).
 *
 * @property int                                 $id
 * @property Blob                                $picture_blob
 * @property bool                                $disable_picture
 * @property bool                                $is_contact
 * @property bool                                $is_user
 * @property bool                                $is_agent
 * @property bool                                $was_agent
 * @property bool                                $can_agent
 * @property bool                                $can_admin
 * @property bool                                $can_billing
 * @property bool                                $can_reports
 * @property bool                                $is_vacation_mode
 * @property bool                                $disable_autoresponses
 * @property string                              $disable_autoresponses_log
 * @property bool                                $is_confirmed
 * @property bool                                $is_deleted
 * @property bool                                $is_disabled
 * @property int                                 $importance
 * @property string                              $creation_system
 * @property string                              $name
 * @property string                              $first_name
 * @property string                              $last_name
 * @property string                              $title_prefix
 * @property string                              $override_display_name
 * @property string                              $summary
 * @property string                              $secret_string
 * @property Language                            $language
 * @property Organization                        $organization
 * @property string                              $organization_position
 * @property bool                                $organization_manager
 * @property string                              $timezone
 * @property string                              $password
 * @property string                              $password_scheme
 * @property string                              $salt
 * @property PersonEmail                         $primary_email
 * @property PersonEmail[]|ArrayCollection       $emails
 * @property PhoneNumber[]                       $phone_numbers
 * @property ArrayCollection|LabelPerson[]       $labels
 * @property ArrayCollection|CustomDataPerson[]  $custom_data
 * @property ArrayCollection|PersonContactData[] $contact_data
 * @property ArrayCollection|Usergroup[]         $usergroups
 * @property TwitterAccount[]                    $twitter_accounts
 * @property TwitterUser[]                       $twitter_users
 * @property PersonPref[]                        $preferences
 * @property PersonUsersourceAssoc[]             $usersource_assoc
 * @property DepartmentPermission                $department_permissions
 * @property \DateTime                           $date_created
 * @property \DateTime                           $date_last_login
 * @property \DateTime                           $date_password_set
 * @property \DateTime                           $date_picture_check
 * @property string                              $browser
 *
 * @method ReportDashboardPermission[] getReportDashboardPermissions()
 *
 * @JMS\ExclusionPolicy("all")
 * @Assert\GroupSequenceProvider
 */
class Person extends DomainObject implements
    HighlightableModelInterface,
    UserInterface,
    \Serializable,
    EquatableInterface,
    LabelsOwner,
    GroupSequenceProviderInterface
{
    const CREATED_WEB_PERSON     = 'web.person';
    const CREATED_WEB_AGENT      = 'web.agent';
    const CREATED_WEB_USERSOURCE = 'web.usersource';
    const CREATED_GATEWAT_PERSON = 'gateway.person';
    const CREATED_WEB_API        = 'web.api';

    const EVENT_PRE_CREATE  = 'person.pre_create';
    const EVENT_POST_CREATE = 'person.post_create';

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The users profile picture.
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $picture_blob = null;

    /**
     * @var bool
     */
    protected $disable_picture = false;

    /**
     * The URL to the users gravatar if any.
     *
     * @deprecated Gravatar url is getting from the person email
     * @see AvatarResolver
     *
     * @var string
     */
    protected $gravatar_url = '';

    /**
     * Is this person a contact (someone we care about seeing)?
     *
     * @var bool
     */
    protected $is_contact = true;

    /**
     * Is this person a user (someone with login credentials)?
     *
     * If a password is set or if they can login with a usersource.
     *
     * @var bool
     */
    protected $is_user = false;

    /**
     * It his person is an agent?
     *
     * @var bool
     */
    protected $is_agent = 0;

    /**
     * Is this person was an agent?
     *
     * @var bool
     */
    protected $was_agent = 0;

    /**
     * Can this person use agent interface?
     *
     * @var bool
     */
    protected $can_agent = 0;

    /**
     * Can this person use admin interface?
     *
     * @var bool
     */
    protected $can_admin = 0;

    /**
     * Can this person use billing interface?
     *
     * @var bool
     */
    protected $can_billing = 0;

    /**
     * Can this person use reports interface?
     *
     *
     * @var bool
     */
    protected $can_reports = 0;

    /**
     * @var bool
     */
    protected $is_vacation_mode = 0;

    /**
     * Autoresponds.
     *
     * @var bool
     */
    protected $disable_autoresponses = 0;

    /**
     * Autoresponses log.
     *
     * @var string
     */
    protected $disable_autoresponses_log = '';

    /**
     * Does person confirmed they email?
     *
     * @var bool
     */
    protected $is_confirmed = false;

    /**
     * Is the user deleted?
     *
     * @var bool
     */
    protected $is_deleted = false;

    /**
     * Is the user disabled?
     *
     * @var bool
     */
    protected $is_disabled = false;

    /**
     * The user importance, 0-5.
     *
     * @var int
     */
    protected $importance = 0;

    /**
     * The way person was created.
     *
     * @var string
     */
    protected $creation_system = 'web.person';

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $name = '';

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $first_name = '';

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $last_name = '';

    /**
     * The users title prefix (Mr., Mrs., etc).
     *
     * @var string
     */
    protected $title_prefix = '';

    /**
     * Overrides the display name of an person in the user interface (agents only).
     *
     * @var string
     */
    protected $override_display_name = '';

    /**
     * The summary field as filled in by agents.
     *
     * @var string
     */
    protected $summary = '';

    /**
     * A secret string used in various hashing or encryption schemes.
     *
     * @var string
     */
    protected $secret_string;

    /**
     * The language associate with the user.
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $language = null;

    /**
     * The users organization.
     *
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization = null;

    /**
     * The persons position at the organization.
     *
     * @var string
     */
    protected $organization_position = '';

    /**
     * True if the person is a manager of their organization.
     *
     * @var bool
     */
    protected $organization_manager = false;

    /**
     * The timezone associated with this user.
     *
     * @var string
     */
    protected $timezone = 'UTC';

    /**
     * Every person has a local login capability with this password and using
     * an email address.
     *
     * @var string
     */
    protected $password = null;

    /**
     * The hashing scheme used with checkPassword(). NULL means default, the built in scheme.
     *
     * @var string
     */
    protected $password_scheme = null;

    /**
     * A salt used to hash the password with.
     *
     * @var string
     */
    protected $salt;

    /**
     * The primary email address used by this account.
     *
     * @var PersonEmail
     * @Assert\NotNull()
     */
    protected $primary_email;

    /**
     * @var ArrayCollection|PersonEmail[]
     *
     * @Assert\Valid()
     * @Assert\Count(min=1)
     */
    protected $emails;

    /**
     * @var PhoneNumber[]
     */
    protected $phone_numbers;

    /**
     * @var ArrayCollection
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     */
    protected $labels;

    /**
     * @var ArrayCollection
     */
    protected $custom_data;

    /**
     * @var ArrayCollection
     *
     * @Assert\Valid()
     */
    protected $contact_data;

    /**
     * Usergroups the user belongs to.
     *
     * @var ArrayCollection
     */
    protected $usergroups;

    /**
     * Twitter accounts this user has access to.
     *
     * @var ArrayCollection
     */
    protected $twitter_accounts;

    /**
     * @var ArrayCollection
     */
    protected $twitter_users;

    /**
     * @var ArrayCollection
     */
    protected $preferences;

    /**
     * Usersource associations.
     *
     * @var ArrayCollection
     */
    protected $usersource_assoc;

    /**
     * @var DepartmentPermission[]
     */
    protected $department_permissions;

    /**
     * The date the user was inserted into the system.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The last time the user logged in.
     *
     * @var \DateTime
     */
    protected $date_last_login = null;

    /**
     * @var \DateTime
     */
    protected $date_password_set = null;

    /**
     * The last time the users gravatar (or other 3rd party image) was checked.
     *
     * @var \DateTime
     */
    protected $date_picture_check = null;

    /**
     * If we have set a password for this user, then the plaintext version will be set here.
     *
     * @var string
     */
    protected $_set_plain_password = null;

    /**
     * Label manager for adding/removing labels.
     *
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * Helper manager for auto-loading functionality onto this object.
     *
     * @var \Orb\Helper\HelperManager
     */
    protected $_helper_manager = null;

    /**
     * The permissions manager helper once its loaded.
     *
     * @var \Application\DeskPRO\People\Helpers\PermissionsManager
     */
    protected $_permissions_manager = null;

    /**
     * True if this is a new record.
     *
     * @var bool
     */
    protected $_is_new_person = false;

    /**
     * @var \Application\DeskPRO\People\PersonChangeTracker
     */
    protected $_person_logger = null;

    /**
     * @var string
     */
    protected $_orig_email_address;

    /**
     * @var string
     */
    protected $_changed_from_primary_email;

    /**
     * @var bool
     */
    protected $_updated_org = false;

    /**
     * The browser person was used last time.
     *
     * @var string
     */
    protected $browser;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * @var ArrayCollection
     *
     * @Assert\Count(max=0, groups="User")
     */
    protected $teams;

    /**
     * @var AgentTeam
     *
     * @Assert\IsNull(groups="User")
     */
    protected $primary_team;

    /**
     * @var AgentTeam
     */
    protected $notes;

    /**
     * @var ReportDashboardPermission
     */
    protected $report_dashboard_permissions;

    /**
     * @var TicketParticipant[]|ArrayCollection
     */
    protected $tickets;

    /**
     * @var ChatConversation[]|ArrayCollection
     */
    protected $chats;

    /**
     * @var AgentData
     *
     * @Assert\IsNull(groups="User")
     * @Assert\Valid()
     */
    protected $agentData;

    /**
     * @var VoiceQueue[]|ArrayCollection
     */
    protected $voiceQueues;

    /**
     * @var PersonOnboarding[]|ArrayCollection
     */
    protected $onboarding;

    /**
     * A "contact person" is simply a person record. They have no login credentials, they are not
     * a full user.
     *
     * (This is really just the same as calling the constructor yourself, but we may need to
     * change this functionality in the future so its a method).
     *
     * @static
     *
     * @return Person
     */
    public static function newContactPerson(array $info = null)
    {
        $person = new self();

        $email = null;
        if (!empty($info['email'])) {
            $email = $info['email'];
            unset($info['email']);
        }

        if ($info) {
            $person->fromArray($info);
        }

        if ($email) {
            $person->addEmailAddressString($email);
        }

        return $person;
    }

    /**
     * A regular person is a person who can log in. They are a full user.
     *
     * @static
     *
     * @return Person
     */
    public static function newRegularPerson()
    {
        $person = new self();

        return $person;
    }

    public function __construct()
    {
        $this->_is_new_person = true;

        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('secret_string', Strings::random(40));
        $this->setModelField('salt', Strings::random(40));

        if (class_exists('Application\\DeskPRO\\App', false)) {
            try {
                $this->setTimezone(App::$container->getSetting('core.default_timezone'));
            } catch (\Exception $e) {
            }
        }
        if (!$this->timezone) {
            $this->setModelField('timezone', 'UTC');
        }

        $this->emails                 = new ArrayCollection();
        $this->usergroups             = new ArrayCollection();
        $this->twitter_accounts       = new ArrayCollection();
        $this->twitter_users          = new ArrayCollection();
        $this->usersource_assoc       = new ArrayCollection();
        $this->contact_data           = new ArrayCollection();
        $this->custom_data            = new ArrayCollection();
        $this->preferences            = new ArrayCollection();
        $this->labels                 = new ArrayCollection();
        $this->phone_numbers          = new ArrayCollection();
        $this->department_permissions = new ArrayCollection();
        $this->teams                  = new ArrayCollection();
        $this->notes                  = new ArrayCollection();
        $this->tickets                = new ArrayCollection();
        $this->chats                  = new ArrayCollection();
        $this->voiceQueues            = new ArrayCollection();
        $this->onboarding             = new ArrayCollection();

        $this->_initPersonLogger();
        $this->_person_logger->recordExtra('person_created', true);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getOverrideDisplayName()
    {
        return $this->override_display_name;
    }

    /**
     * @return string
     */
    public function getTitlePrefix()
    {
        return $this->title_prefix;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return Blob
     */
    public function getPictureBlob()
    {
        return $this->picture_blob;
    }

    public function _initPersonLogger()
    {
        if ($this->_person_logger) {
            return;
        }
        $person_logger        = new \Application\DeskPRO\People\PersonChangeTracker($this);
        $this->_person_logger = $person_logger;
        $this->addPropertyChangedListener($person_logger);
    }

    public function hasPerm($name)
    {
        return $this->getPermissionsManager()->hasPerm($name);
    }

    /**
     * @return PersonUsersourceAssoc[]|ArrayCollection
     */
    public function getUsersourceAssoc()
    {
        return $this->usersource_assoc;
    }

    /**
     * @param string $type 'agent' or 'user'
     *
     * @return bool
     */
    public function hasDeskproUsersource($type)
    {
        foreach ($this->usersource_assoc as $assoc) {
            $us = $assoc->usersource;
            if ($us->type == $type && $us->source_type == 'Application\DeskPRO\Usersource\Adapter\DeskPRO') {
                return true;
            }
        }

        return false;
    }

    /**
     * Try to guess an org name based on profile info.
     *
     * @return string
     */
    public function guessOrganizationName()
    {
        if ($this->organization) {
            return $this->organization->name;
        }

        foreach ($this->emails as $email) {
            if (FreeEmailProviders::isFreeEmailDomain($email->email_domain)) {
                continue;
            }

            $name = $email->email_domain;
            if ($pos = strpos($name, '.')) {
                $name = substr($name, 0, $pos);
            }

            return ucfirst($name);
        }

        return;
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
        $this->_updated_org = true;
        if ($org_id) {
            $org = App::getEntityRepository('DeskPRO:Organization')->find($org_id);
            $this->setModelField('organization', $org);
        } else {
            $this->setModelField('organization', null);
        }
    }

    /**
     * @return string
     */
    public function getOrganizationPosition()
    {
        return $this->organization_position;
    }

    /**
     * Is this a guest?
     *
     * @return bool
     */
    public function isGuest()
    {
        return false;
    }

    /**
     * Is this user a contact? (Not a user that can login?).
     *
     * @return bool
     */
    public function isContact()
    {
        return !$this->isUser();
    }

    /**
     * Is this a user? A 'user' is simply a person who we know can log in. For example, they
     * have a password or are attached to a usersource.
     *
     * If the person is NOT a user, they are simply a contact record in the database.
     *
     * @return bool
     */
    public function isUser()
    {
        return (bool) $this->is_user;
    }

    /**
     * @param bool $isUser
     *
     * @return $this
     */
    public function setIsUser($isUser)
    {
        $this->setModelField('is_user', $isUser);

        return $this;
    }

    /**
     * True if the Person has every confirmed themselves. A user is confirmed by
     * clicking a link in their email at least once.
     *
     * Each app in DeskPRO has different requirements for user confirmations. Some apps
     * might require the user to validate, some might not.
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->is_confirmed;
    }

    /**
     * @param bool $yesno
     *
     * @return $this
     */
    public function setIsDisabled($yesno)
    {
        $this->setModelField('is_disabled', $yesno);

        return $this;
    }

    /**
     * Is agent.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->can_admin;
    }

    /**
     * @return bool
     */
    public function wasAgent()
    {
        return $this->was_agent;
    }

    /**
     * @return bool
     */
    public function canAgent()
    {
        return $this->can_agent;
    }

    /**
     * @return bool
     */
    public function canAdmin()
    {
        return $this->can_admin;
    }

    /**
     * @return bool
     */
    public function isActiveAgent()
    {
        return $this->is_agent && !$this->is_deleted && !$this->is_disabled;
    }

    /**
     * @param bool $yesno
     *
     * @return $this
     */
    public function setIsAgent($yesno)
    {
        $this->setModelField('is_agent', $yesno);

        if ($yesno) {
            $this->setModelField('is_confirmed', true);
        } else {
            $this->setModelField('agentData', null);
            $this->setModelField('primary_team', null);

            foreach ($this->teams as $team) {
                $this->removeTeam($team);
            }
        }

        return $this;
    }

    /**
     * @param bool $yesno
     *
     * @return $this
     */
    public function setIsDeleted($yesno)
    {
        $this->setModelField('is_deleted', $yesno);

        return $this;
    }

    /**
     * @param bool $yesno
     *
     * @return $this
     */
    public function setCanAdmin($yesno)
    {
        if ($yesno) {
            $this['can_reports'] = true;
        }

        $this->setModelField('can_admin', $yesno);

        return $this;
    }

    /**
     * @return bool
     * @return $this
     */
    public function isDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @param bool $yesno
     *
     * @return $this
     */
    public function setCanAgent($yesno)
    {
        $this->setModelField('can_agent', $yesno);

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getCanBilling()
    {
        // If they are an admin, they can use billing
        // (because they could just log in to admin and set themselves as billing!)
        if ($this->can_admin) {
            return true;
        }

        return $this->can_billing;
    }

    /**
     * @return bool|int
     */
    public function getRealCanBilling()
    {
        return $this->can_billing;
    }

    /**
     * @param string
     *
     * @return $this
     */
    public function setCreationSystem($creationSystem = null)
    {
        $this->setModelField('creation_system', $creationSystem);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreationSystem()
    {
        return $this->creation_system;
    }

    /**
     * Add a new helper.
     *
     * @param string $name    Name of the helper class
     * @param array  $options
     */
    public function loadHelper($name, array $options = [])
    {
        $classname = 'Application\\DeskPRO\\People\\Helpers\\'.$name;
        $manager   = $this->getHelperManager();

        if (!$manager->hasHelper($name)) {
            $manager->addHelper(new $classname($this, $options));
        }
    }

    /**
     * Get a registered helper.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getHelper($name)
    {
        // force load helper if was not loaded
        $this->loadHelper($name);

        return $this->getHelperManager()->getHelper($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isHelperLoader($name)
    {
        return $this->getHelperManager()->hasHelper($name);
    }

    /**
     * @return ArrayCollection|Usergroup[]
     */
    public function getUsergroups()
    {
        return $this->usergroups;
    }

    /**
     * @return ArrayCollection|Usergroup[]
     *
     * @AppAssert\UniqueCollection()
     */
    public function getPublicUsergroups()
    {
        return $this->usergroups->filter(function (Usergroup $group) {
            return !$group->is_agent_group && !in_array($group->sys_name, ['everyone', 'registered']) && $group->is_enabled;
        });
    }

    /**
     * @return ArrayCollection|Usergroup[]
     *
     * @AppAssert\UniqueCollection()
     */
    public function getPublicAgentgroups()
    {
        return $this->usergroups->filter(function (Usergroup $group) {
            return $group->is_agent_group && $group->is_enabled;
        });
    }

    /**
     * @return bool
     */
    public function isOrganizationManager()
    {
        return $this->organization_manager;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $context
     * @param bool   $forceAgentData
     *
     * @return array
     */
    public function getAllowedDepartments($context = 'tickets', $forceAgentData = false)
    {
        $this->loadHelper('AgentPermissions');

        return $this->_onNotCallable('getalloweddepartments', [$context, $forceAgentData]);
    }

    /**
     * @param string $context
     * @param bool   $forceAgentData
     *
     * @return array
     */
    public function getDisallowedDepartments($context = 'tickets', $forceAgentData = false)
    {
        $this->loadHelper('AgentPermissions');

        return $this->_onNotCallable('getdisalloweddepartments', [$context, $forceAgentData]);
    }

    protected function _onNotCallable($name, $arguments)
    {
        if ($this->_helper_manager) {
            $name_l = strtolower($name);
            if ($this->_helper_manager->isNameCallable($name_l)) {
                return $this->_helper_manager->callName($name_l, $arguments);
            }
        }

        if (strpos($name, 'getfield') === 0 && $field_id = Strings::extractRegexMatch('#^getfield(\d+)$#', $name)) {
            return $this->renderCustomField($field_id, 'text');
        }

        return parent::_onNotCallable($name, $arguments);
    }

    public function __get($name)
    {
        if ($this->_helper_manager) {
            $name_l = strtolower($name);
            if ($this->_helper_manager->isNameCallable($name_l)) {
                return $this->_helper_manager->callName($name_l, []);
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
     *
     * @param bool $id_fallback
     *
     * @return string|null
     */
    public function getDisplayName($id_fallback = true)
    {
        if ($this->first_name and $this->last_name) {
            return $this->first_name.' '.$this->last_name;
        } elseif ($this->name) {
            return $this->name;
        } elseif ($this->last_name) {
            return $this->last_name;
        } elseif ($this->first_name) {
            return $this->first_name;
        } elseif ($this->primary_email) {
            // try to get a nice name from the email address
            return Strings::getNameFromEmail($this->primary_email->getEmail());
        } elseif ($this->getPrimaryPhoneNumberText()) {
            return $this->getPrimaryPhoneNumberText();
        } elseif ($id_fallback) {
            return 'ID-'.$this->id;
        }

        return;
    }

    /**
     * Gets the display name to be display.
     *
     * @return null|string
     */
    public function getDisplayNameUser()
    {
        if ($this->is_agent && $this->override_display_name) {
            return $this->override_display_name;
        }

        return $this->getDisplayName();
    }

    /**
     * Gets this person's name with the title prefix.
     *
     * @return string|null
     */
    public function getNameWithTitle()
    {
        $name = $this->getDisplayName(true);
        if ($this->title_prefix) {
            $name = $this->title_prefix.' '.$name;
        }

        return $name;
    }

    /**
     * Gets this persons name and their primary email address.
     *
     * @return string
     */
    public function getDisplayContact()
    {
        $display = $this->getDisplayName();
        if ($this->getPrimaryEmailAddress() && $display != $this->getPrimaryEmailAddress()) {
            $display .= " <{$this->getPrimaryEmailAddress()}>";
        }

        return $display;
    }

    /**
     * Gets this persons name and their primary email address and wraps each in a span tag. Useful
     * when you want to apply style to each part individually.
     *
     * @return string
     */
    public function getDisplayContactHtml()
    {
        $name = @htmlspecialchars($this->getDisplayName(), ENT_QUOTES, 'UTF-8');

        $display   = [];
        $display[] = '<span class="contact-name">'.$name.'</span>';

        if ($this->getPrimaryEmailAddress() && $name != $this->getPrimaryEmailAddress()) {
            $email     = @htmlspecialchars("<{$this->getPrimaryEmailAddress()}>", ENT_QUOTES, 'UTF-8');
            $display[] = '<span class="contact-email">'.$email.'</span>';
        }

        return implode(' ', $display);
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
        $try = [
            ['fn', ' ', 'ln', ' ', '(e)'],
            ['fi', ' ', 'ln', ' ', '(e)'],
            ['fn', ' ', 'li', ' ', '(e)'],
            ['n', ' ', '(e)'],
            ['fn', ' ', 'ln'],
            ['fn', ' ', 'li'],
            ['fi', 'ln'],
            ['fi', 'li', ' ', '(e)'],
            ['fi', 'li'],
            ['e'],
            ['n'],
        ];

        $shortest     = null;
        $shortest_len = null;

        foreach ($try as $elements) {
            $display = [];

            foreach ($elements as $el) {
                switch ($el) {
                    case 'n':
                        if (!$this->name) {
                            continue 2;
                        }
                        $display[] = $this->name;
                        break;

                    case 'fi':
                        if (!$this->first_name) {
                            continue 2;
                        }
                        $display[] = $this->first_name[0];
                        break;

                    case 'fn':
                        if (!$this->first_name) {
                            continue 2;
                        }
                        $display[] = $this->first_name;
                        break;

                    case 'li':
                        if (!$this->last_name) {
                            continue 2;
                        }
                        $display[] = $this->last_name[0];
                        break;

                    case 'ln':
                        if (!$this->last_name) {
                            continue 2;
                        }
                        $display[] = $this->last_name;
                        break;

                    case '(e)':
                    case 'e':
                        if (!$this->getPrimaryEmailAddress()) {
                            continue 2;
                        }

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
            $len     = strlen($display);

            if ($len <= $max_len) {
                return $display;
            }

            if ($shortest === null or $len < $shortest_len) {
                $shortest     = $display;
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
     * Set the importance of this user.
     *
     * @param int $importance
     */
    public function setImportance($importance)
    {
        $old              = $this->importance;
        $this->importance = Numbers::bound($importance, 0, 5);
        $this->_onPropertyChanged('importance', $old, $this->importance);
    }

    public function getApiToken()
    {
        return App::getEntityRepository('DeskPRO:ApiToken')->getTokenForPerson($this);
    }

    /**
     * Check to see if a password is the same one we have on record. Used with local auth.
     *
     * @param  $plain_password
     *
     * @return bool
     */
    public function checkPassword($plain_password)
    {
        // It may be a token login from dp:login-token
        if ($this->id && strlen($plain_password) > 55) {
            $secret = sha1($this->secret_string.$this->salt);
            if (Util::checkStaticSecurityToken($plain_password, $secret)) {
                $GLOBALS['DP_LOGIN_VIA_TOKEN'] = true;

                return true;
            }
        }

        // Allows a define to be added to config to override a users password:
        // define('DP_OVERRIDE_USER_PASS', '20001:mypassword');
        if ($this->id && defined('DP_OVERRIDE_USER_PASS') && strpos(DP_OVERRIDE_USER_PASS, ':') !== false) {
            list($id, $override_pass) = explode(':', DP_OVERRIDE_USER_PASS, 2);
            if ($this->id == $id || $id == '*') {
                if ($override_pass === $plain_password) {
                    return true;
                }
            }
        }

        return $this->getPasswordSchemeHandler()->checkPassword($this, $this->password, $plain_password);
    }

    /**
     * Sets the hashed form of the password for this user. Used with local auth.
     *
     * @param string $plain_password     The password to set
     * @param bool   $expire_immediately True to make the user enter a new password next time they log in
     *
     * @return string
     */
    public function setPassword($plain_password, $expire_immediately = false)
    {
        // If we're setting the password, we're now using the default
        // password scheme so remove the old one. eg an imported user just changed their password
        $this->setModelField('password_scheme', 'bcrypt');

        // When a password is set, then they're a user now
        $this->setModelField('is_user', true);

        $hash                      = $this->hashPassword($plain_password);
        $this->_set_plain_password = $plain_password;

        $this->setModelField('password', $hash);

        if ($expire_immediately) {
            $this->setModelField(
                'date_password_set',
                \DateTime::createFromFormat('Y-m-d H:i:s', PasswordPolicyValidator::MAGIC_PASSWORD_EXPIRED_TRIGGER_DATE)
            );
        } else {
            $this->setModelField('date_password_set', new \DateTime());
        }

        if ($this->id) {
            $token = App::getEntityRepository('DeskPRO:ApiToken')->getTokenForPerson($this);
            if ($token) {
                $token->regenerateToken();
                App::getOrm()->persist($token);
            }
        }

        return $this->password;
    }

    /**
     * Set the raw password field (ie already hashed).
     *
     * @param $password
     *
     * @return $this
     */
    public function setRawPassword($password)
    {
        $this->setModelField('password', $password);

        return $this;
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
     * Get the raw password hash.
     *
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->password;
    }

    /**
     * Create a new password hash using the salt and algorithm used with this user.
     *
     * @param string $plain_password The password to hash
     *
     * @return string
     */
    public function hashPassword($plain_password)
    {
        return $this->getPasswordSchemeHandler()->hashPassword($this, $plain_password);
    }

    /**
     * @return \Application\DeskPRO\People\PasswordSchemeInterface
     */
    public function getPasswordSchemeHandler()
    {
        if ($this->password_scheme === null) {
            $scheme = 'deskpro4original';
        } else {
            $scheme = $this->password_scheme;
        }

        return App::getSystemObject('password_scheme', ['scheme' => $scheme]);
    }

    /**
     * Add a preference value to this user.
     *
     * @param PersonPref $pref
     */
    public function addPreference(PersonPref $pref)
    {
        $this->preferences->add($pref);
        $pref->person = $this;
        $this->_onPropertyChanged('preferences', $this->preferences, $this->preferences);
    }

    /**
     * Set the value of a preference. Will update if it exists, or create a new one
     * if it doesnt.
     *
     * @param  $pref
     * @param  $value
     *
     * @return PersonPref
     */
    public function setPreference($pref_name, $value)
    {
        $pref = App::getEntityRepository('DeskPRO:PersonPref')->getForPerson($pref_name, $this);
        if (!$pref) {
            $pref         = new PersonPref();
            $pref['name'] = $pref_name;
            $this->addPreference($pref);
        }

        $pref['value'] = $value;

        return $pref;
    }

    /**
     * Get the value of a preference as it's currently stored.
     *
     * @param string $name
     * @param mixed  $default Default Value for the preference
     *
     * @return mixed
     */
    public function getPref($name, $default = null)
    {
        foreach ($this->preferences as $pref) {
            if ($pref->name == $name) {
                return $pref->getValue();
            }
        }

        if ($default === null && $name == 'agent.ticket_reverse_order') {
            return App::getSetting('core_tickets.default_ticket_reverse_order');
        }

        return $default;
    }

    /**
     * Get an array of named preferences.
     *
     * @param string $names ...
     *
     * @return array
     */
    public function getNamedPrefs()
    {
        if (func_num_args() == 1) {
            $names   = [];
            $names[] = func_get_arg(0);
        } else {
            $names = func_get_args();
        }

        $names = array_fill_keys(array_values($names), true);
        $ret   = [];

        foreach ($this->preferences as $pref) {
            if (isset($names[$pref->name])) {
                $ret[$pref->name] = $pref->getValue();
            }
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
     * Get the users language.
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function getLanguage()
    {
        if ($this->language) {
            return $this->language;
        }

        return App::getDataService('Language')->getDefault();
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        $l = $this->getLanguage();

        return $l ? $l->getId() : 0;
    }

    /**
     * Set language.
     *
     * @param Language|null $language
     *
     * @return $this
     */
    public function setLanguage(Language $language = null)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setLanguageId($id)
    {
        $lang = App::getDataService('Language')->get($id);
        if (!$lang) {
            $lang = null;
        }

        $this['language'] = $lang;

        return $this;
    }

    /**
     * Get the locale string.
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
     * Returns the ISO-8601 representation of the day of the week that this
     * person has selected as their start of the week.
     *
     * 1 = Monday, ..., 7 = Sunday
     *
     * @return int
     */
    public function getStartOfWeek()
    {
        return 1;
    }

    /**
     * Load a group of user prefs.
     *
     * @param string $pref_group
     *
     * @return array
     */
    public function loadPrefGroup($pref_group)
    {
        $pref_group     = rtrim($pref_group, '.'); // incase it was supplied with dot
        $pref_group_len = strlen($pref_group) + 1; // used with trimming below

        $ret = [];
        foreach ($this->preferences as $pref) {
            if (strpos($pref->name, $pref_group) === 0) {
                $pref_name       = substr($pref->name, $pref_group_len);
                $ret[$pref_name] = $pref->getValue();
            }
        }

        return $ret;
    }

    /**
     * Get the value of a usergroup permission.
     *
     * @param string $name The permission name
     *
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
        return $this->getPermissionsManager()->getUsergroupIds();
    }

    /**
     * Check if a member is in a particular group.
     *
     * @param $usergroup_id
     *
     * @return bool
     */
    public function isMemberOfUsergroup($usergroup_id)
    {
        if (is_object($usergroup_id)) {
            $usergroup_id = $usergroup_id->getId();
        }

        return in_array($usergroup_id, $this->getUsergroupIds());
    }

    /**
     * @param PersonContactData $contact_data
     */
    public function addContactData(PersonContactData $contact_data)
    {
        $this->contact_data->add($contact_data);
        $contact_data['person'] = $this;
        $this->_onPropertyChanged('contact_data', $this->contact_data, $this->contact_data);
    }

    /**
     * @param PersonContactData $contact_data
     */
    public function removeContactData(PersonContactData $contact_data)
    {
        $this->contact_data->removeElement($contact_data);
        $this->_onPropertyChanged('contact_data', $this->contact_data, $this->contact_data);
    }

    /**
     * Reset contact data
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetContactData()
    {
        $this->contact_data->clear();

        return $this;
    }

    /**
     * @param null $type
     *
     * @return PersonContactData[]|ArrayCollection
     */
    public function getContactData($type = null)
    {
        if (!$type) {
            return $this->contact_data;
        }

        $ret = [];

        foreach ($this->contact_data as $cd) {
            if ($cd->contact_type == $type) {
                $ret[] = $cd;
            }
        }

        return $ret;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int|CustomDefPerson $field_id
     *
     * @return CustomDataPerson
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefPerson) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        return;
    }

    /**
     * @return CustomDataPerson[]|ArrayCollection
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    public function removeCustomDataForField(CustomDefPerson $field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        $change = false;
        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $change = true;
                $this->custom_data->removeElement($data);

                if ($parent_id) {
                    $this->getStateChangeRecorder()->record("custom_data.$parent_id", $data, null, true);
                } else {
                    $this->getStateChangeRecorder()->record("custom_data.$field_id", $data, null, true);
                }
            }
        }

        if ($change) {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }
    }

    public function removeCustomData(CustomDataPerson $data)
    {
        $this->custom_data->removeElement($data);
        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * @param Collection $custom_data
     */
    public function setCustomData(Collection $custom_data)
    {
        foreach ($custom_data as $cd) {
            $cd->person = $this;
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Set custom field data for a particular field.
     *
     * @param int   $field_id
     * @param mixed $value
     *
     * @return mixed
     */
    public function setCustomDataField($field_id, $value_type, $value)
    {
        $custom_data = $this->getCustomDataForField($field_id);
        $is_new      = false;

        if (!$custom_data) {
            if ($value === null) {
                return;
            }

            $is_new = true;

            $field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($field_id);
            if (!$field) {
                throw new \Exception("Invalid field_id `$field_id`");
            }
            $custom_data          = new CustomDataPerson();
            $custom_data['field'] = $field;
        }

        $field = $custom_data->field;
        if ($field->parent) {
            foreach ($this->custom_data as $d) {
                if ($d->field && $d->field->parent && $d->field->parent['id'] == $field->parent['id']) {
                    $this->custom_data->removeElement($d);
                }
            }
        }

        $this->custom_data->removeElement($custom_data);

        if ($value === null) {
            $this->custom_data->removeElement($custom_data);

            return;
        }

        if ($field->getTypeName() == 'choice') {
            return;
        }

        $custom_data[$value_type] = $value;

        if ($is_new) {
            $this->addCustomData($custom_data);
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);

        return $custom_data;
    }

    /**
     * Add a custom data item to this ticket.
     *
     * @param CustomDataPerson $data
     */
    public function addCustomData(CustomDataPerson $data)
    {
        $this->custom_data->add($data);
        $data->setPerson($this);

        $field     = $data->field;
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        if ($parent_id) {
            $this->getStateChangeRecorder()->record("custom_data.$parent_id", null, $data, true);
        } else {
            $this->getStateChangeRecorder()->record("custom_data.$field_id", null, $data, true);
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Reset custom data
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetCustomData()
    {
        $this->custom_data->clear();

        return $this;
    }

    /**
     * Render a custom field.
     *
     * @deprecated
     */
    public function renderCustomField($field_id, $context = 'html')
    {
        $f_def = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($field_id);

        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, [$f_def]);

        $value    = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
        $rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

        return trim($rendered);
    }

    /**
     * Check if this ticket has a custom field.
     *
     * @param $field_id
     *
     * @return bool
     */
    public function hasCustomField($field_id)
    {
        foreach ($this->custom_data as $data) {
            if ($data->field['id'] == $field_id) {
                return true;
            }
        }

        foreach ($this->custom_data as $data) {
            if ($data->field->parent and $data->field->parent['id'] == $field_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a display array for a specific field.
     *
     * @param $field_id
     *
     * @return array|mixed|null
     */
    public function getCustomFieldDisplayArray($field_id)
    {
        $data = $this->getCustomDataForField($field_id);
        if (!$data) {
            return;
        }

        $ticket_field_defs      = App::getApi('custom_fields.people')->getEnabledFields();
        $ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(
            [$data],
            $ticket_field_defs
        );

        $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray(
            $ticket_field_defs,
            $ticket_data_structured
        );

        $custom_fields = array_pop($custom_fields);

        return $custom_fields;
    }

    /**
     * returns the number that this person marked as his/her primary number.
     *
     * @return PhoneNumber
     */
    public function getPrimaryPhoneNumber()
    {
        return $this->phone_numbers && $this->phone_numbers->first() ? $this->phone_numbers->first() : null;
    }

    /**
     * get the number of primary number.
     *
     * @return string
     */
    public function getPrimaryPhoneNumberText()
    {
        $phone_number = $this->getPrimaryPhoneNumber();

        return $phone_number ? $phone_number->number : '';
    }

    /**
     * get the 2 character country code of primary number.
     *
     * @return string
     */
    public function getPrimaryPhoneNumberRegion()
    {
        $phone_number = $this->getPrimaryPhoneNumber();

        $region = $phone_number ? $phone_number->region : null;

        if (!$region) {
            $region = '';
        }

        return $region;
    }

    public function setPrimaryPhoneNumber(PhoneNumber $number = null)
    {
        // note that while this is a 1-many relationship, we ensure in this method that we only have 1
        // or 0 PhoneNumber objects in the collection.
        // if there is a null, we just remove any number that might have been in our collection
        if (!$number) {
            $this->phone_numbers->clear();
            $this->_onPropertyChanged('phone_numbers', $this->phone_numbers, $this->phone_numbers);

            return;
        }

        // we have a number, make sure its the only one in our collection here
        $number->person = $this;
        if ($current_number = $this->getPrimaryPhoneNumber()) {
            if ($current_number->getId() == $number->getId()) {
                $this->_onPropertyChanged('phone_numbers', $this->phone_numbers, $this->phone_numbers);

                return;
            } else {
                $old = new ArrayCollection([$current_number]);
                $this->phone_numbers->clear();
                $this->phone_numbers->add($number);
                $this->_onPropertyChanged('phone_numbers', $old, $this->phone_numbers);
            }
        }
        $this->phone_numbers->add($number);
        $this->_onPropertyChanged('phone_numbers', $this->phone_numbers, $this->phone_numbers);
    }

    /**
     * Get the primary email address, or null if this person has none.
     *
     * @return string
     */
    public function getPrimaryEmailAddress()
    {
        if (!$this->primary_email) {
            return;
        }

        return $this->primary_email['email'];
    }

    public function getPrimaryEmail()
    {
        return $this->primary_email;
    }

    public function pickEmailAddress($search)
    {
        $search = strtolower($search);

        if (count($this->emails) == 1 || !trim($search)) {
            return $this->getPrimaryEmailAddress();
        }

        foreach ($this->emails as $e) {
            $email = strtolower($e->email);
            if (strpos($email, $search) !== false) {
                return $email;
            }
        }

        return $this->getPrimaryEmailAddress();
    }

    /**
     * @return PersonEmail[]|ArrayCollection
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param PersonEmail $email
     *
     * @return $this
     */
    public function addEmail(PersonEmail $email)
    {
        if (!$this->primary_email) {
            $this->setPrimaryEmail($email);
        }

        $this->emails->add($email);
        $email->setPerson($this);
        $this->_onPropertyChanged('emails', null, $this->emails);

        return $this;
    }

    /**
     * @param PersonEmail[] $emails
     */
    public function setEmails($emails)
    {
        if ($emails instanceof Collection) {
            $emails = $emails->toArray();
        }

        $emails = array_values($emails);

        // unset primary if it's not among new $emails
        if (!in_array($this->primary_email, $emails)) {
            $this->primary_email = null;
        }

        // set first email as primary if no primary email set yet
        if (!$this->primary_email && count($emails)) {
            $this->setPrimaryEmail($emails[0]);
        }
        $addresses = array_map(
            function (PersonEmail $email) {
                return $email->getEmail();
            },
            $emails
        );
        $this->setEmailAddresses($addresses);
    }

    /**
     * @return string
     */
    public function getAccountOriginalEmailAddress()
    {
        if (!$this->id) {
            return $this->getEmailAddress() ?: '';
        }

        if ($this->_orig_email_address === null) {
            $db                        = App::$container->getDb();
            $this->_orig_email_address = $db->fetchColumn('
              SELECT value_str
              FROM people_prefs
              WHERE person_id = ? AND name = ?
            ', [$this->id, 'sys.account_original_email_address']);
            if (!$this->_orig_email_address) {
                $this->_orig_email_address = $this->getEmailAddress() ?: '';
            }
        }

        return $this->_orig_email_address;
    }

    /**
     * @return string|null
     */
    public function getAccountTrackingId()
    {
        $email = $this->getAccountOriginalEmailAddress();
        if ($email) {
            return sha1($email);
        }

        return;
    }

    /**
     * Alias for getPrimaryEmailAddress.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->getPrimaryEmailAddress();
    }

    /**
     * @param bool $skipPrimary
     * @param bool $validatedOnly
     *
     * @return array
     */
    public function getEmailAddresses($skipPrimary = false, $validatedOnly = true)
    {
        $arr = [];
        foreach ($this->emails as $email) {
            if ($skipPrimary && $email->getEmail() === $this->primary_email->getEmail()) {
                continue;
            }
            if ($email->isValidated() || !$validatedOnly) {
                $arr[] = $email->getEmail();
            }
        }

        return $arr;
    }

    /**
     * @param array $emails
     *
     * @return $this
     */
    public function setEmailAddresses(array $emails)
    {
        $set_emails = array_map(
            function ($email_address) {
                return strtolower($email_address);
            },
            $emails
        );
        $have_emails = array_map(
            function (PersonEmail $email) {
                return strtolower($email->getEmail());
            },
            $this->emails->toArray()
        );

        $add_emails = array_diff($set_emails, $have_emails);
        $del_emails = array_diff($have_emails, $set_emails);

        foreach ($add_emails as $email_address) {
            $email = new PersonEmail();
            $email
                ->setPerson($this)
                ->setEmail($email_address)
                ->setIsValidated(true);

            $this->addEmailAddress($email);
        }

        foreach ($del_emails as $email_address) {
            $email = $this->findEmailAddress($email_address);
            if ($email) {
                $this->removeEmailAddressId($email->getId());
            }
        }

        return $this;
    }

    /**
     * Check if the user has an email address.
     *
     * @param string $email_address
     *
     * @return bool
     */
    public function hasEmailAddress($email_address)
    {
        return $this->getEmailByAddress($email_address) ? true : false;
    }

    /**
     * Return the PersonEmail object assocaited to this person for an email address.
     *
     * @param string $email_address
     *
     * @return PersonEmail|false
     */
    public function getEmailByAddress($email_address)
    {
        $email_address = strtolower($email_address);
        if ($this->primary_email && strtolower($this->primary_email->getEmail()) === $email_address) {
            return $this->primary_email;
        }

        if ($this->emails) {
            foreach ($this->emails as $email) {
                if (strtolower($email->getEmail()) === $email_address) {
                    return $email;
                }
            }
        }

        return false;
    }

    /**
     * Get the primary email address ID.
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
     * @param PersonEmail $email
     */
    public function setPrimaryEmail(PersonEmail $email = null)
    {
        if ($email) {
            if (!$this->_changed_from_primary_email
                && $this->primary_email
                && $this->primary_email->email !== $email->email
            ) {
                $this->_changed_from_primary_email = $this->primary_email->email;
            }

            $email->setPerson($this);
        }

        $this->setModelField('primary_email', $email);
    }

    /**
     * Sets the primary email address on the account.
     *
     * @param string $email_address
     * @param bool   $validated
     *
     * @return PersonEmail
     */
    public function setEmail($email_address, $validated = false)
    {
        $email = null;
        foreach ($this->emails as $existing_email) {
            if ($existing_email->getEmail() === $email_address) {
                $email = $existing_email;
            }
        }

        if (!$email) {
            $email = new PersonEmail();
            $email->setEmail($email_address);

            $this->addEmailAddress($email);
        }
        if ($validated) {
            $email->setIsValidated(true);
        }

        $this->setPrimaryEmail($email);

        return $email;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        $email = $this->getPrimaryEmail();
        if (!$email) {
            $email = $this->emails->first();
        }

        return $email ? $email->getEmail() : null;
    }

    /**
     * Get email addresses that are validated.
     *
     * @return array
     */
    public function getValidatedEmails()
    {
        $ret = [];

        foreach ($this->emails as $email) {
            if ($email['is_validated']) {
                $ret[] = $email;
            }
        }

        return $ret;
    }

    /**
     * Add an email address.
     *
     * @param PersonEmail $email
     *
     * @return PersonEmail
     */
    public function addEmailAddress(PersonEmail $email)
    {
        if (!$this->primary_email && $this->emails->count() < 1) {
            $this->setPrimaryEmail($email);
        }

        foreach ($this->emails as $old) {
            if ($email->email === $old->email) {
                return $email;
            }
        }

        $this->emails->add($email);
        $this->_onPropertyChanged('emails', $this->emails, $this->emails);

        $email->person = $this;

        return $email;
    }

    /**
     * Adds an email address string. This is same as addEmailAddress except
     * we take care of creating the PersonEmail object here.
     *
     * @param string $email
     *
     * @return PersonEmail
     */
    public function addEmailAddressString($email)
    {
        $emailObj = new PersonEmail();
        $emailObj->setEmail($email);

        $this->addEmailAddress($emailObj);

        return $emailObj;
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
     *
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

        if ($the_email and $this->primary_email['id'] == $the_email['id']) {
            $next_email       = null;
            $next_valid_email = null;
            foreach ($this->emails as $index => $email) {
                if (!$next_email) {
                    $next_email = $email;
                }
                if (!$next_valid_email and $email['is_validated']) {
                    $next_valid_email = $email;
                }

                if ($next_email and $next_valid_email) {
                    break;
                }
            }

            if ($next_valid_email) {
                $this->setPrimaryEmail($next_valid_email);
                $em->persist($this);
            } elseif ($next_email) {
                $this->setPrimaryEmail($next_email);
                $em->persist($this);
            }
        }

        $this->_onPropertyChanged('emails', $this->emails, $this->emails);

        return $the_email;
    }

    /**
     * Reset emails collection
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetEmails()
    {
        // todo hot fix
        foreach ($this->emails as $email) {
            App::getOrm()->remove($email);
        }

        App::getOrm()->flush();

        $this->primary_email = null;
        $this->emails->clear();

        return $this;
    }

    public function getEmailId($email_id)
    {
        foreach ($this->emails as $index => $email) {
            if ($email['id'] == $email_id) {
                return $email;
            }
        }

        return;
    }

    /**
     * Get the email record for a specific address.
     *
     * @return PersonEmail
     */
    public function findEmailAddress($email_address)
    {
        $email_address = strtolower($email_address);

        if ($this->primary_email && strtolower($this->primary_email->email) == $email_address) {
            return $this->primary_email;
        }

        foreach ($this->emails as $email) {
            if (strtolower($email['email']) == $email_address) {
                return $email;
            }
        }

        return;
    }

    /**
     * Add a new usergroup.
     *
     * @param Usergroup $usergroup
     *
     * @return bool
     */
    public function addUsergroup(Usergroup $usergroup)
    {
        if ($this->hasUsergroup($usergroup)) {
            return false;
        }

        $this->usergroups->add($usergroup);
        $this->_onPropertyChanged('usergroups', $this->usergroups, $this->usergroups);

        return true;
    }

    /**
     * Remove usergroup.
     *
     * @param Usergroup $usergroup
     *
     * @return bool
     */
    public function removeUsergroup(Usergroup $usergroup)
    {
        $this->usergroups->removeElement($usergroup);
        $this->_onPropertyChanged('usergroups', $this->usergroups, $this->usergroups);

        return true;
    }

    /**
     * Remove all usergroups
     * todo add onPropertyChanged() if change tracking is needed.
     *
     * @return $this
     */
    public function resetUsergroups()
    {
        $this->usergroups->clear();

        return $this;
    }

    /**
     * Check if the user belongs to a usergroup.
     *
     * @param $usergroup
     *
     * @return bool
     */
    public function hasUsergroup(Usergroup $usergroup)
    {
        return $this->usergroups->contains($usergroup);
    }

    /**
     * @param Label $label
     *
     * @return $this
     */
    public function addLabel(Label $label)
    {
        foreach ($this->labels as $l) {
            if ($l->getLabel() === $label->getLabel()) {
                return;
            }
        }
        $label['person'] = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', $this->labels, $this->labels);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', $this->labels, $this->labels);
        }
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLabels()
    {
        $this->labels->clear();
        $this->_onPropertyChanged('labels', $this->labels, $this->labels);
    }

    public function removeLabelByString($l)
    {
        foreach ($this->labels as $label) {
            if ($l !== $label->label) {
                continue;
            }

            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', $this->labels, $this->labels);
            break;
        }
    }

    /**
     * Reset labels.
     *
     * @return $this
     */
    public function resetLabels()
    {
        foreach ($this->labels as $data) {
            $this->labels->removeElement($data);
        }

        $this->_onPropertyChanged('labels', null, $this->labels);

        return $this;
    }

    public function addNote(PersonNote $note)
    {
        $note->person = $this;
        $this->notes->add($note);
        $this->_onPropertyChanged('notes', $this->notes, $this->notes);
    }

    public function removeNote(PersonNote $note)
    {
        $this->notes->removeElement($note);
        $this->_onPropertyChanged('notes', $this->notes, $this->notes);
    }

    public function getUsergroupSetKey()
    {
        if ($this->usergroups instanceof \Doctrine\Common\Collections\Collection) {
            $this->usergroups = $this->usergroups->toArray();
        }

        return Usergroup::generateUsergroupSetKey($this->usergroups);
    }

    /**
     * Set the picture blob.
     *
     * @param \Application\DeskPRO\Entity\Blob $blob
     *
     * @return $this
     */
    public function setPictureBlob(Blob $blob = null)
    {
        $this->setModelField('picture_blob', $blob);

        return $this;
    }

    /**
     * Gets the URL to a picture for the person. Note that this will always return
     * a path to an image, even if it's the default. If you need to check for the
     * existance of an image, use hasPicture.
     *
     * @deprecated Use the AvatarResolver instead
     *
     * @return null|string
     */
    public function getPictureUrl($size = 80, $secure = null, $default = false)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $url = false;
        if ($this->hasPicture() && !$default) {
            if ($this->picture_blob && $this->picture_blob->isImage()) {
                $url = App::get('router.default')->generate(
                    'serve_blob_sizefit',
                    [
                        'blob_auth_id' => $this->picture_blob->getAuthId(),
                        'filename'     => $this->picture_blob->getFilenameSafe(),
                        's'            => $size,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } elseif (App::getSetting('core.use_gravatar') && $this->primary_email && $this->primary_email->getId()) {
                $url = $this->getGravatarUrl($size, $secure);
            }
        }

        if (!$url) {
            if ($this->organization && $this->organization->hasPicture()) {
                return $this->organization->getPictureUrl($size, $secure);
            } else {
                $url = App::get('router.default')->generate(
                    'serve_default_picture',
                    [
                        's'        => $size,
                        'size-fit' => 1,
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
        }

        if ($secure) {
            $url = preg_replace('#^http:#', 'https:', $url);
        }

        return $url;
    }

    public function getRawGravatarUrl()
    {
        if ($this->primary_email) {
            return rtrim($this->primary_email->getGravatarUrl(true), '?');
        }

        return;
    }

    public function getGravatarUrl($size = 80, $secure = null)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $url = $this->primary_email ? $this->primary_email->getGravatarUrl($secure) : '';
        if ($size != 80) {
            $url .= '&s='.urlencode($size);
        }

        if ($this->organization && $this->organization->hasPicture()) {
            $url .= '&d='.urlencode($this->organization->getPictureUrl($size, $secure));
        } else {
            if ($this->is_agent) {
                $url .= '&d=mm';
            } else {
                $url .= '&d=mm';
            }
        }

        return $url;
    }

    /**
     * Does this user have a picture associated with their account?
     *
     * @return bool
     */
    public function hasPicture()
    {
        if ($this->disable_picture) {
            return false;
        }

        if ($this->picture_blob) {
            return true;
        }

        if ($this->primary_email || App::getSetting('core.use_gravatar')) {
            return true;
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
        if ($this->_is_new_person) {
            return true;
        }

        // Hack for users created outside of doctrine in PersonFromEmailProcessor
        if ($this->id && isset($GLOBALS['DP_CREATED_PEOPLE_IDS'][$this->id])) {
            return true;
        }

        return false;
    }

    /**
     * Set this persons organization and position.
     *
     * @param Organization $org
     * @param string       $position
     * @param bool         $manager
     *
     * @return $this
     */
    public function setOrganization(Organization $org = null, $position = '', $manager = false)
    {
        $this->_updated_org = true;
        if (!$org) {
            $this->setModelField('organization', $org);
            $this->setModelField('organization_position', '');
            $this->setModelField('organization_manager', false);
        } else {
            $this->setModelField('organization', $org);
            $this->setModelField('organization_position', $position);
            $this->setModelField('organization_manager', (bool) $manager);
        }

        return $this;
    }

    public function getTwitterAccountIds()
    {
        $output = [];
        foreach ($this->twitter_accounts as $account) {
            $output[] = $account['id'];
        }

        return $output;
    }

    /**
     * @return TwitterAccount[]
     */
    public function getTwitterAccounts()
    {
        return $this->twitter_accounts;
    }

    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function getKeys()
    {
        $keys   = parent::getKeys();
        $keys[] = 'display_name';

        return $keys;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $name = preg_replace('# {2,}#', ' ', $name);
        $this->setModelField('name', $name);

        $parts = Strings::rexplode(' ', $name, 2);
        $this->setModelField('first_name', $parts[0]);
        $this->setModelField('last_name', isset($parts[1]) ? $parts[1] : '');

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setFirstName($name)
    {
        $this->setModelField('first_name', $name);
        $this->setModelField('name', $name.' '.$this->last_name);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setLastName($name)
    {
        $this->setModelField('last_name', $name);
        $this->setModelField('name', $this->first_name.' '.$name);

        return $this;
    }

    /**
     * @param string $title_prefix
     *
     * @return $this
     */
    public function setTitlePrefix($title_prefix)
    {
        $this->setModelField('title_prefix', $title_prefix ?: '');

        return $this;
    }

    /**
     * Set the last time this usersource was used.
     *
     * @param \DateTime $time The time to set, or null to set now
     */
    public function setLastLoginAt(\DateTime $time = null)
    {
        if (!$time) {
            $time = new \DateTime();
        }

        $this->setModelField('date_last_login', $time);
    }

    public function setDisablePicture($yn = false)
    {
        $this->setModelField('disable_picture', $yn);

        if ($yn) {
            $this['picture_blob'] = null;
        }
    }

    /**
     * Get a Person ID from some parameter that might be a person, already a
     * person ID, or some object that knows about a person ID.
     *
     * @param mixed $person
     *
     * @return int
     */
    public static function smartPersonId($person)
    {
        if (is_int($person)) {
            return $person;
        } elseif (ctype_digit($person)) {
            return (int) $person;
        } elseif (\is_object($person)) {
            if ($person instanceof self) {
                return (int) $person['id'];
            }
        } elseif (isset($person['person_id'])) {
            return (int) $person['person_id'];
        }

        return;
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

    public function _savePersonLogs()
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }

        if (
            $this->_changed_from_primary_email
            && !$this->_person_logger->isNewPerson()
            && $this->id
        ) {
            try {
                $db   = App::$container->getDb();
                $orig = $db->fetchColumn('
                  SELECT value_str
                  FROM people_prefs
                  WHERE person_id = ? AND name = ?
                ', [$this->id, 'sys.account_original_email_address']);
                if (!$orig) {
                    $db->insertIgnore('people_prefs', [
                        'person_id' => $this->id,
                        'name'      => 'sys.account_original_email_address',
                        'value_str' => $this->_changed_from_primary_email,
                    ]);
                }
            } catch (\Exception $e) {
            }
        }

        if ($this->_person_logger) {
            $this->_person_logger->done();
            $this->_person_logger = null;
            if ($this->_person_logger) {
                $this->removePropertyChangedListener($this->_person_logger);
            }
            $this->_initPersonLogger();
        }

        if ($this->_updated_org) {
            $new_org = $this->organization ? $this->organization->getId() : null;
            App::getDb()->update('tickets', ['organization_id' => $new_org], ['person_id' => $this->id]);
            App::getDb()->update(
                'tickets_search_active',
                ['organization_id' => $new_org],
                ['person_id'       => $this->id]
            );
        }
    }

    public function _presavePerson()
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }

        // If we're loaded, then set default timezone from setting
        if (!$this->timezone && class_exists('Application\\DeskPRO\\App')) {
            try {
                $this->setTimezone(App::$container->getSetting('core.default_timezone'));
            } catch (\Exception $e) {
            }
        }

        if ($this->_person_logger) {
            $this->_person_logger->preSave();
        }
    }

    public function setTimezone($tz)
    {
        $tz = trim($tz);
        if (!$tz) {
            $tz = 'UTC';
        }

        // Make sure its valid
        try {
            $dt = new \DateTimeZone($tz);
        } catch (\Exception $e) {
            $tz = 'UTC';
        }

        $this->setModelField('timezone', $tz);

        return $this;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function getTimezone()
    {
        if (!$this->timezone) {
            return 'UTC';
        }

        return $this->timezone;
    }

    public function getRealTimezone()
    {
        return $this->timezone;
    }

    public function getDateTimezone()
    {
        try {
            return new \DateTimeZone($this->getTimezone());
        } catch (\Exception $e) {
            return new \DateTimeZone('UTC');
        }
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return new \DateTime('now', $this->getDateTimezone());
    }

    /**
     * @param $time
     *
     * @return \DateTime
     */
    public function getDateForTime($time)
    {
        return new \DateTime($time, $this->getDateTimezone());
    }

    public function getTimezoneOffset($as_string = false)
    {
        $user_offset = $this->getDateTimezone()->getOffset(new \DateTime('now'));
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

    /**
     * @return int
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->getTimezoneOffset() * 3600;
    }

    /**
     * @param bool   $val
     * @param string $reason
     */
    public function setDisableAutoresponses($val, $reason = null)
    {
        $val = (bool) $val;

        $this->setModelField('disable_autoresponses', $val);
        if (!$val) {
            $this->setModelField('disable_autoresponses_log', null);
        } else {
            if (!$reason) {
                $reason = 'Unknown';
            }
            $reason .= ' ('.date('M j Y @ H:i').' UTC)';
            $this->setModelField('disable_autoresponses_log', $reason);
        }
    }

    /**
     * @param string $organization_position
     *
     * @return $this
     */
    public function setOrganizationPosition($organization_position)
    {
        if (!$organization_position) {
            $organization_position = '';
        }

        $this->setModelField('organization_position', $organization_position);

        return $this;
    }

    public function hasSla(Sla $sla)
    {
        foreach ($this->slas as $person_sla) {
            if ($person_sla->id == $sla->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRememberMeCookieCode()
    {
        return \Orb\Util\Util::generateStaticSecurityToken(sha1(App::getAppSecret().$this->secret_string));
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function validateRememberMeCookieCode($code)
    {
        return \Orb\Util\Util::checkStaticSecurityToken($code, sha1(App::getAppSecret().$this->secret_string));
    }

    public function _postPersist()
    {
        // Unset permissions manager so it'll be relaoded now that the user is registered
        $this->_permissions_manager = null;
    }

    /**
     * @return \Application\DeskPRO\People\PersonChangeTracker
     */
    public function getChangeTracker()
    {
        $this->_initPersonLogger();

        return $this->_person_logger;
    }

    public function getDataForWidget()
    {
        $data = [];
        foreach ([
                     'id',
                     'name',
                     'first_name',
                     'last_name',
                     'title_prefix',
                     'creation_system',
                     'organization_position',
                 ] as $key) {
            $data[$key] = $this->$key;
        }
        $data['date_created'] = $this->date_created->getTimestamp();
        $data['email']        = $this->getPrimaryEmailAddress();

        if ($this->organization) {
            $data['organization'] = ['id' => $this->organization->id, 'name' => $this->organization->name];
        }
        if ($this->language) {
            $data['language'] = ['id' => $this->language->id, 'title' => $this->language->title];
        }

        if (count($this->labels)) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        $customFields   = App::getSystemService('person_fields_manager')->getDisplayArrayForObject($this);
        $data['custom'] = [];
        foreach ($customFields as $field) {
            $data['custom'][$field['id']] = [
                'id'    => $field['id'],
                'title' => $field['title'],
                'value' => isset($field['value']['value']) ? $field['value']['value'] : false,
            ];
        }

        return $data;
    }

    public function addTeam(AgentTeam $team)
    {
        if (!$this->teams->contains($team)) {
            if (!$this->primary_team) {
                $this->setModelField('primary_team', $team);
            }
            $this->teams->add($team);
            $this->_onPropertyChanged('teams', $this->teams, $this->teams);
        }

        $team->addPerson($this);
    }

    public function getTeamIds()
    {
        $ids = [];

        foreach ($this->teams as $team) {
            $ids[] = $team->id;
        }

        return $ids;
    }

    public function removeTeam(AgentTeam $team)
    {
        $this->teams->removeElement($team);
        $this->_onPropertyChanged('teams', $this->teams, $this->teams);
        $team->removePerson($this);

        if ($this->primary_team === $team) {
            $this->setModelField('primary_team', $this->teams->first() ?: null);
        }
    }

    /**
     * @return ArrayCollection|\Application\DeskPRO\Entity\AgentTeam[]
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * @param AgentTeam $agentTeam
     *
     * @return $this
     */
    public function setPrimaryTeam(AgentTeam $agentTeam = null)
    {
        $this->setModelField('primary_team', $agentTeam);
        if ($agentTeam) {
            $this->addTeam($agentTeam);
        }

        return $this;
    }

    /**
     * @param $checkFirst
     *
     * @return \Application\DeskPRO\Entity\AgentTeam
     */
    public function getPrimaryTeam($checkFirst = true)
    {
        if ($this->primary_team) {
            return $this->primary_team;
        }

        if ($checkFirst && $first = $this->teams->first()) {
            return $first;
        }

        return;
    }

    /**
     * @param PersonOnboarding $onboarding
     *
     * @return $this
     */
    public function addOnboarding(PersonOnboarding $onboarding)
    {
        $existing = $this->onboarding->filter(function (PersonOnboarding $existOnboarding) use ($onboarding) {
            return $onboarding->getOnboardingClass() === $existOnboarding->getOnboardingClass();
        });

        if (!$existing->count()) {
            $onboarding->setPerson($this);
            $this->onboarding->add($onboarding);

            $this->_onPropertyChanged('onboarding', $this->onboarding, $this->onboarding);
        }

        return $this;
    }

    /**
     * @param bool  $primary
     * @param bool  $deep
     * @param array $visited
     *
     * @return array
     *
     * @deprecated see \Application\DeskPRO\DependencyInjection\SystemServices\PersonApiDataFactoryService
     *             The factory service is better suited. This method is still widely used, but its encourages
     *             to use the factory service going forward
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }
        }

        if ($this->organization) {
            $data['organization_usergroups'] = [];
            foreach ($this->organization->usergroups as $group) {
                $data['organization_usergroups'][] = $group->toApiData(false, false, $visited);
            }

            if (empty($data['organization'])) {
                $data['organization'] = $this->organization->toApiData($primary, $deep, $visited);
            }
        }

        if ($this->language) {
            $data['language'] = $this->language->toApiData();
        }

        foreach ([
                     'id',
                     'first_name',
                     'last_name',
                     'name',
                     'display_name',
                     'override_display_name',
                     'can_admin',
                     'can_billing',
                     'can_reports',
                     'timezone',
                 ] as $k) {
            $data[$k] = $this[$k];
        }

        if ($this->primary_email) {
            $data['primary_email'] = [
                'id'    => (int) $this->primary_email->id,
                'email' => $this->primary_email->email,
            ];
        }

        $pp                    = $this->getPrimaryPhoneNumber();
        $data['primary_phone'] = $pp ? $pp->toApiData() : [];
        $data['phone_numbers'] = [];

        foreach ($this->phone_numbers as $phoneNumber) {
            $data['phone_numbers'][] = $phoneNumber->toApiData();
        }

        $data['emails'] = [];
        foreach ($this->emails as $eml) {
            $data['emails'][] = ['id' => $eml->id, 'email' => $eml->email];
        }

        $data['usergroup_ids']  = [];
        $data['agentgroup_ids'] = [];
        foreach ($this->usergroups as $ug) {
            if ($ug->is_agent_group) {
                $data['agentgroup_ids'][] = $ug->id;
            } else {
                $data['usergroup_ids'][] = $ug->id;
            }
        }

        $data['usergroup_ids'][] = 2;

        $data['usergroup_ids']  = Arrays::castToType($data['usergroup_ids'], 'int');
        $data['agentgroup_ids'] = Arrays::castToType($data['agentgroup_ids'], 'int');

        $urlTemplate        = $this->getPictureUrl('{{size}}');
        $defaultUrlTemplate = $this->getPictureUrl('{{size}}', null, true);
        $encodedTag         = urlencode('{{size}}');

        $pictureSizes = [80, 64, 50, 45, 32, 22, 16];
        foreach ($pictureSizes as $pictureSize) {
            $data['picture_url_'.$pictureSize]         = $urlTemplate ? str_replace($encodedTag, $pictureSize, $urlTemplate) : null;
            $data['default_picture_url_'.$pictureSize] = $defaultUrlTemplate ? str_replace($encodedTag, $pictureSize, $defaultUrlTemplate) : null;
        }

        $data['picture_url']         = $data['picture_url_80'];
        $data['default_picture_url'] = $data['default_picture_url_80'];

        // Render custom fields to text values
        $field_manager = App::getContainer()->getSystemService('person_fields_manager');
        $field_manager->addApiData($this, $data);

        return $data;
    }

    public function toBasicApiData()
    {
        $agent_data = [
            'id'              => $this->id,
            'name'            => $this->name,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'display_name'    => $this->getDisplayName(),
            'is_agent'        => $this->is_agent,
            'can_agent'       => $this->can_agent,
            'can_admin'       => $this->can_admin,
            'can_billing'     => $this->can_billing,
            'can_reports'     => $this->can_reports,
            'is_deleted'      => $this->is_deleted,
            'is_disabled'     => $this->is_disabled,
            'date_last_login' => $this->date_last_login ? $this->date_last_login->format('Y-m-d H:i:s') : null,
            'primary_email'   => [
                'id'    => $this->primary_email ? $this->primary_email->id : null,
                'email' => $this->primary_email ? $this->primary_email->email : null,
            ],
            'picture_url'    => $this->getPictureUrl(),
            'picture_url_80' => $this->getPictureUrl(80),
            'picture_url_64' => $this->getPictureUrl(64),
            'picture_url_50' => $this->getPictureUrl(50),
            'picture_url_45' => $this->getPictureUrl(45),
            'picture_url_32' => $this->getPictureUrl(32),
            'picture_url_22' => $this->getPictureUrl(22),
            'picture_url_16' => $this->getPictureUrl(16),
        ];

        return $agent_data;
    }

    /**
     * Set ElasticSearch highlight data.
     *
     * @param array $highlights array of highlight strings
     */
    public function setElasticHighlights(array $highlights)
    {
        if (!empty($highlights)) {
            $this->_search_highlights = $highlights;
        }
    }

    /**
     * Get Elasticsearch highlight data.
     *
     * @param null $field
     *
     * @return array|null
     */
    public function getElasticHighlights($field = null)
    {
        if (is_null($field)) {
            return $this->_search_highlights;
        } else {
            if (isset($this->_search_highlights[$field])) {
                return $this->_search_highlights[$field];
            } else {
                return;
            }
        }
    }

    /**
     * Get person`s phones.
     *
     * @return array
     */
    public function getPhoneNumbersArray()
    {
        return array_map(
            function ($phone) {
                return [
                    'number' => $phone->number,
                    'ext'    => $phone->ext,
                    'label'  => $phone->label,
                ];
            },
            $this->phone_numbers->toArray()
        );
    }

    /**
     * @return PhoneNumber[]|ArrayCollection
     */
    public function getPhoneNumbers()
    {
        return $this->phone_numbers;
    }

    /**
     * Count of tickets person was assigned.
     *
     * @return int
     */
    public function getTicketsCount()
    {
        return $this->tickets->count();
    }

    /**
     * Count of tickets person participating.
     *
     * @return int
     */
    public function getChatsCount()
    {
        return $this->chats->count();
    }

    /**
     * List of person emails.
     *
     * @return array
     */
    public function getEmailsArray()
    {
        return ListUtils::filterMap($this->getEmails(), function ($email) {
            /* @var \Application\DeskPRO\Entity\PersonEmail $email */
            return $email->getEmail();
        });
    }

    /**
     * @param AgentData $agentData
     *
     * @return $this
     */
    public function setAgentData(AgentData $agentData = null)
    {
        $this->setModelField('agentData', $agentData);

        return $this;
    }

    /**
     * @return AgentData
     */
    public function getAgentData()
    {
        return $this->agentData;
    }

    /**
     * @return VoiceQueueAgent[]|ArrayCollection
     */
    public function getVoiceQueues()
    {
        return $this->voiceQueues;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = PersonRepository::class;

        $metadata->setPrimaryTable(
            [
                'name'    => 'people',
                'indexes' => [
                    'is_agent_idx'     => ['columns' => [0 => 'is_agent']],
                    'was_agent_idx'    => ['columns' => [0 => 'was_agent']],
                    'is_confirmed_idx' => ['columns' => [0 => 'is_confirmed']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->addLifecycleCallback('_initPersonLogger', 'postLoad');
        $metadata->addLifecycleCallback('_presavePerson', 'prePersist');
        $metadata->addLifecycleCallback('_postPersist', 'postPersist');
        $metadata->addLifecycleCallback('_savePersonLogs', 'postPersist');
        $metadata->addLifecycleCallback('_savePersonLogs', 'postUpdate');

        if (defined('DP_INTERFACE') && DP_INTERFACE != 'install') {
            foreach ([Events::prePersist, Events::postPersist, Events::preUpdate, Events::postUpdate] as $event) {
                $metadata->addEntityListener(
                    $event,
                    PersonChangeLogListener::class,
                    'on'.ucfirst($event)
                );
            }
            foreach ([Events::prePersist, Events::preUpdate] as $event) {
                $metadata->addEntityListener(
                    $event,
                    PersonOnboardingListener::class,
                    $event
                );
            }
        }

        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'gravatar_url',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'gravatar_url',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'disable_picture',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'disable_picture',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_contact',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_contact',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_user',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_user',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_agent',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_agent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'was_agent',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'was_agent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'can_agent',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'can_agent',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'can_admin',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'can_admin',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'can_billing',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'can_billing',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'can_reports',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'can_reports',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_vacation_mode',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_vacation_mode',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'disable_autoresponses',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'disable_autoresponses',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'disable_autoresponses_log',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'disable_autoresponses_log',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_confirmed',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_confirmed',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_deleted',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_deleted',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_disabled',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_disabled',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'importance',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'importance',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'creation_system',
                'type'       => 'string',
                'length'     => 20,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'creation_system',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'name',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'first_name',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'first_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'last_name',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'last_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title_prefix',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title_prefix',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'override_display_name',
                'type'       => 'string',
                'length'     => 200,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'override_display_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'summary',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'summary',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'secret_string',
                'type'       => 'string',
                'length'     => 40,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'secret_string',
                'dpqlAccess' => false,
                'dpApi'      => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'organization_position',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'organization_position',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'organization_manager',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'organization_manager',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'timezone',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'timezone',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'password',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'password',
                'dpqlAccess' => false,
                'dpApi'      => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'password_scheme',
                'type'       => 'string',
                'length'     => 20,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'password_scheme',
                'dpqlAccess' => false,
                'dpApi'      => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'salt',
                'type'       => 'string',
                'length'     => 40,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'salt',
                'dpqlAccess' => false,
                'dpApi'      => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_last_login',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_last_login',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_password_set',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_password_set',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_picture_check',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_picture_check',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'browser',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'browser',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'picture_blob',
                'targetEntity' => Blob::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'fetch'        => ClassMetadata::FETCH_EAGER,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'picture_blob_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'language',
                'targetEntity' => Language::class,
                'mappedBy'     => null,
                'cascade'      => ['persist'],
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'organization',
                'targetEntity' => Organization::class,
                'mappedBy'     => null,
                'inversedBy'   => 'members',
                'fetch'        => ClassMetadata::FETCH_EAGER,
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'organization_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToOne(
            [
                'fieldName'    => 'primary_email',
                'targetEntity' => PersonEmail::class,
                'cascade'      => ['persist', 'detach'],
                'mappedBy'     => null,
                'inversedBy'   => null,
                'fetch'        => ClassMetadata::FETCH_EAGER,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'primary_email_id',
                        'referencedColumnName' => 'id',
                        'unique'               => true,
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'emails',
                'targetEntity'  => PersonEmail::class,
                'cascade'       => ['persist', 'detach'],
                'mappedBy'      => 'person',
                'dpApi'         => true,
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => LabelPerson::class,
                'cascade'       => ['remove', 'persist', 'merge', 'detach'],
                'mappedBy'      => 'person',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'custom_data',
                'targetEntity'  => CustomDataPerson::class,
                'cascade'       => ['remove', 'persist', 'merge', 'detach'],
                'mappedBy'      => 'person',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'contact_data',
                'targetEntity' => PersonContactData::class,
                'cascade'      => ['remove', 'persist', 'merge', 'detach'],
                'mappedBy'     => 'person',
                'indexBy'      => 'id',
                'dpApi'        => true,
                'dpApiDeep'    => true,
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'usergroups',
                'targetEntity' => Usergroup::class,
                'inversedBy'   => 'people',
                'cascade'      => ['persist', 'merge'],
                'joinTable'    => [
                    'name'        => 'person2usergroups',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'person_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'usergroup_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'preferences',
                'targetEntity' => PersonPref::class,
                'cascade'      => ['persist', 'remove', 'merge'],
                'mappedBy'     => 'person',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'usersource_assoc',
                'targetEntity' => PersonUsersourceAssoc::class,
                'mappedBy'     => 'person',
                'cascade'      => ['persist', 'remove'],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'twitter_users',
                'targetEntity' => PersonTwitterUser::class,
                'mappedBy'     => 'person',
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'twitter_accounts',
                'targetEntity' => TwitterAccount::class,
                'mappedBy'     => 'persons',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'notes',
                'targetEntity' => PersonNote::class,
                'mappedBy'     => 'person',
                'cascade'      => ['persist', 'remove'],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'phone_numbers',
                'targetEntity'  => PhoneNumber::class,
                'mappedBy'      => 'person',
                'cascade'       => ['persist', 'detach'],
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'department_permissions',
                'targetEntity' => DepartmentPermission::class,
                'mappedBy'     => 'person',
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'     => 'tickets',
                'targetEntity'  => TicketParticipant::class,
                'mappedBy'      => 'person',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
            ]
        );

        $metadata->mapOneToMany([
                                    'fieldName'    => 'report_dashboard_permissions',
                                    'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardPermission',
                                    'mappedBy'     => 'person',
                                ]);

        $metadata->mapManyToMany(
            [
                'fieldName'    => 'teams',
                'mappedBy'     => 'members',
                'dpApi'        => true,
                'targetEntity' => AgentTeam::class,
                'joinTable'    => [
                    'name'               => 'agent_team_members',
                    'joinColumns'        => [['name' => 'person_id', 'onDelete' => 'CASCADE']],
                    'inverseJoinColumns' => [['name' => 'team_id', 'onDelete' => 'CASCADE']],
                ],
            ]
        );

        $metadata->mapManyToMany(
            [
                'fieldName'    => 'chats',
                'mappedBy'     => 'participants',
                'dpApi'        => false,
                'targetEntity' => ChatConversation::class,
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'primary_team',
                'dpApi'        => true,
                'targetEntity' => AgentTeam::class,
                'nullable'     => true,
                'joinColumns'  => [
                    [
                        'name'                 => 'primary_team_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                    ],
                ],
            ]
        );

        $metadata->mapOneToOne([
            'fieldName'     => 'agentData',
            'targetEntity'  => AgentData::class,
            'inversedBy'    => 'person',
            'cascade'       => ['persist', 'remove'],
            'orphanRemoval' => true,
            'joinColumns'   => [
                [
                    'name'                 => 'agent_data_id',
                    'referencedColumnName' => 'id',
                    'unique'               => true,
                    'nullable'             => true,
                    'columnDefinition'     => null,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);

        $metadata->mapOneToMany([
            'fieldName'    => 'voiceQueues',
            'targetEntity' => VoiceQueueAgent::class,
            'mappedBy'     => 'agent',
            'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
        ]);

        $metadata->mapOneToMany([
            'fieldName'     => 'onboarding',
            'targetEntity'  => PersonOnboarding::class,
            'mappedBy'      => 'person',
            'orphanRemoval' => true,
            'cascade'       => ['persist', 'remove', 'merge'],
        ]);
    }

    public function clear()
    {
        if ($this->_permissions_manager) {
            $this->_permissions_manager->clear();
        }
        if ($this->_person_logger) {
            $this->_person_logger->clear();
        }
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        if ($this->isAdmin()) {
            return ['ROLE_ADMIN'];
        }
        if ($this->isAgent()) {
            return ['ROLE_AGENT'];
        }

        return ['ROLE_USER'];
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getId();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->_set_plain_password = null;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->id);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     */
    public function unserialize($serialized)
    {
        $this->id = unserialize($serialized);
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        return $this->id == $user->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['Person'];

        if ($this->is_agent) {
            $groups[] = 'Agent';
        } else {
            $groups[] = 'User';
        }

        return $groups;
    }
}
