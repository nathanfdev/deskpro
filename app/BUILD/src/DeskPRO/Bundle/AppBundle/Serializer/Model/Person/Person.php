<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Person.
 */
class Person extends ExtendedPerson
{
    /**
     * The user`s profile picture.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Blob>")
     *
     * @var Blob
     */
    protected $pictureBlob;

    /**
     * True if user`s picture disabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $disablePicture;

    /**
     * The URL to the users gravatar if any.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $gravatarUrl;

    /**
     * Is this person a contact?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isContact;

    /**
     * Are autoresponses disabled?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $disableAutoresponses;

    /**
     * Disabled autoresponses log.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $disableAutoresponsesLog;

    /**
     * Has person confirmed their email?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isConfirmed;

    /**
     * Is the user deleted?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isDeleted;

    /**
     * Is the user disabled?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isDisabled;

    /**
     * The way person was created.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $creationSystem;

    /**
     * Overrides the display name of an person in the user interface (agents only).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $overrideDisplayName;

    /**
     * Person name and email address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $displayContact;

    /**
     * Person name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $displayName;

    /**
     * The summary field as filled in by agents.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $summary;

    /**
     * Default person`s language.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    protected $language;

    /**
     * The person`s organization.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     *
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization;

    /**
     * The persons position at the organization.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $organizationPosition;

    /**
     * True if the person is a manager of their organization.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $organizationManager;

    /**
     * The timezone associated with this user.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $timezone;

    /**
     * The date the user was inserted into the system.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * The date the user was logged in last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateLastLogin;

    /**
     * The browser person was used last time.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $browser;

    /**
     * Usergroups the user belongs to.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var ArrayCollection
     */
    protected $userGroups;

    /**
     * Usergroups the user belongs to.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Usergroup>>")
     *
     * @var ArrayCollection
     */
    protected $agentGroups;

    /**
     * Labels associated with this user.
     *
     * @JMS\Type("deferred<array<label<Application\DeskPRO\Entity\LabelPerson>>>")
     *
     * @var \Application\DeskPRO\Entity\Labels\Label[]
     */
    protected $labels;

    /**
     * Emails belong to user.
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     *
     * @var array
     */
    protected $primaryEmail;

    /**
     * Emails belong to user.
     *
     * @JMS\Type("deferred<collection<to_string<Application\DeskPRO\Entity\PersonEmail>>>")
     *
     * @var array
     */
    protected $emails;

    /**
     * Phone numbers belong to user.
     *
     * @JMS\Type("deferred<collection<Application\DeskPRO\Entity\PersonPhoneNumber>>")
     *
     * @var array
     */
    protected $phoneNumbers;

    /**
     * Overall tickets count assigned to user.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $ticketsCount;

    /**
     * Overall count of chats user participating.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $chatsCount;

    /**
     * Custom persons data.
     *
     * @JMS\Type("deferred<custom_data<array>>")
     *
     * @var CustomDataPerson[]
     */
    protected $fields;

    /**
     * Contacts for this user.
     *
     * @JMS\Type("deferred<collection>")
     *
     * @var \Application\DeskPRO\Entity\PersonContactData[]
     */
    protected $contactData;

    /**
     * Agent teams.
     *
     * @JMS\Type("deferred<collection<entity<Application\DeskPRO\Entity\AgentTeam>>>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam[]
     */
    protected $teams;

    /**
     * Primary agent team.
     *
     * @JMS\Type("deferred<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $primaryTeam;

    /**
     * Brands.
     *
     * @JMS\Type("deferred<collection<entity<Application\DeskPRO\Entity\Brand>>>")
     *
     * @var \Application\DeskPRO\Entity\Brand[]
     */
    protected $brands;

    /**
     * {@inheritdoc}
     *
     * @param array $customData
     */
    public function __construct(PersonEntity $person, Avatar $avatar)
    {
        parent::__construct($person, $avatar);

        $this->pictureBlob             = $person->picture_blob;
        $this->disablePicture          = $person->disable_picture;
        $this->gravatarUrl             = $person->getGravatarUrl();
        $this->isContact               = $person->is_contact;
        $this->disableAutoresponses    = $person->disable_autoresponses;
        $this->disableAutoresponsesLog = $person->disable_autoresponses_log;
        $this->isConfirmed             = $person->isConfirmed();
        $this->isDeleted               = $person->isDeleted();
        $this->isDisabled              = $person->isDisabled();
        $this->creationSystem          = $person->getCreationSystem();
        $this->overrideDisplayName     = $person->getOverrideDisplayName();
        $this->displayContact          = $person->getDisplayContact();
        $this->displayName             = $person->getDisplayName();
        $this->summary                 = $person->getSummary();
        $this->language                = $person->getLanguage();
        $this->organization            = $person->getOrganization();
        $this->organizationPosition    = $person->getOrganizationPosition();
        $this->organizationManager     = $person->isOrganizationManager();
        $this->timezone                = $person->getTimezone();
        $this->dateCreated             = $person->getDateCreated();
        $this->dateLastLogin           = $person->date_last_login;
        $this->browser                 = $person->browser;
        $this->userGroups              = $person->getPublicUsergroups();
        $this->agentGroups             = $person->getPublicAgentgroups();
        $this->ticketsCount            = $person->getTicketsCount();
        $this->chatsCount              = $person->getChatsCount();
    }

    /**
     * @param CallbackDeferredProperty $customData
     *
     * @return $this
     */
    public function setCustomData($customData = null)
    {
        $this->fields = $customData;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $contactData
     *
     * @return $this->primaryEmail = $person->getPrimaryEmail();
     */
    public function setContactData($contactData = null)
    {
        $this->contactData = $contactData;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $phoneNumbers
     *
     * @return $this
     */
    public function setPhoneNumbers($phoneNumbers = null)
    {
        $this->phoneNumbers = $phoneNumbers;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $agentTeams
     *
     * @return $this
     */
    public function setAgentTeams($agentTeams = null)
    {
        $this->teams = $agentTeams;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $brands
     *
     * @return $this
     */
    public function setBrands($brands = null)
    {
        $this->brands = $brands;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $primaryTeam
     *
     * @return $this
     */
    public function setPrimaryTeam($primaryTeam = null)
    {
        $this->primaryTeam = $primaryTeam;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $labels
     *
     * @return $this
     */
    public function setLabels($labels = null)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @param CallbackDeferredProperty $emails
     *
     * @return $this
     */
    public function setEmails($emails = null)
    {
        $this->emails = $emails;

        return $this;
    }
}
