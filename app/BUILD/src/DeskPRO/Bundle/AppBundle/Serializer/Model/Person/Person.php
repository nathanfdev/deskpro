<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Person.
 */
class Person
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

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
     * Is this person a user?
     *
     * @var bool
     */
    protected $isUser;

    /**
     * Is this person an agent?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isAgent;

    /**
     * Was this person an agent?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $wasAgent;

    /**
     * Is person allowed to use agent interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canAgent;

    /**
     * Is person allowed to use admin interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canAdmin;

    /**
     * Is person allowed to use billing interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canBilling;

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
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $firstName;

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $lastName;

    /**
     * The users title prefix (Mr., Mrs., etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $titlePrefix;

    /**
     * Overrides the display name of an person in the user interface (agents only).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $overrideDisplayName;

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
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\LabelPerson>>")
     *
     * @var \Application\DeskPRO\Entity\Labels\Label[]
     */
    protected $labels;

    /**
     * Main user`s email.
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     *
     * @var string
     */
    protected $primaryEmail;

    /**
     * Emails belong to user.
     *
     * @JMS\Type("collection<to_string<Application\DeskPRO\Entity\PersonEmail>>")
     *
     * @var array
     */
    protected $emails;

    /**
     * Users avatar.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     *
     * @var Avatar
     */
    protected $avatar;

    /**
     * Is user online?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $online;

    /**
     * Date when user was last seen online.
     *
     * @JMS\Type("deferred<DateTime>")
     *
     * @var \DateTime
     */
    protected $lastSeen;

    /**
     * Phone numbers belong to user.
     *
     * @JMS\Type("array<array<string>>")
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
     * @JMS\Type("custom_data<array>")
     *
     * @var CustomDataPerson[]
     */
    protected $fields;

    /**
     * Contacts for this user.
     *
     * @JMS\Type("collection")
     *
     * @var \Application\DeskPRO\Entity\PersonContactData[]
     */
    protected $contactData;

    /**
     * Agent teams.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam[]
     */
    protected $teams;

    /**
     * Primary agent team.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $primaryTeam;

    /**
     * Agent data.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\AgentData")
     *
     * @var AgentData
     */
    protected $agentData;

    /**
     * Constructor.
     *
     * @param PersonEntity $person
     */
    public function __construct(PersonEntity $person)
    {
        $this->id                      = $person->getId();
        $this->pictureBlob             = $person->picture_blob;
        $this->disablePicture          = $person->disable_picture;
        $this->gravatarUrl             = $person->getGravatarUrl();
        $this->isContact               = $person->is_contact;
        $this->isUser                  = $person->isUser();
        $this->isAgent                 = $person->isAgent();
        $this->wasAgent                = $person->was_agent;
        $this->canAgent                = $person->can_agent;
        $this->canAdmin                = $person->can_admin;
        $this->canBilling              = $person->getRealCanBilling();
        $this->disableAutoresponses    = $person->disable_autoresponses;
        $this->disableAutoresponsesLog = $person->disable_autoresponses_log;
        $this->isConfirmed             = $person->isConfirmed();
        $this->isDeleted               = $person->isDeleted();
        $this->isDisabled              = $person->isDisabled();
        $this->creationSystem          = $person->creation_system;
        $this->name                    = $person->name;
        $this->firstName               = $person->first_name;
        $this->lastName                = $person->last_name;
        $this->titlePrefix             = $person->title_prefix;
        $this->overrideDisplayName     = $person->override_display_name;
        $this->summary                 = $person->summary;
        $this->language                = $person->getLanguage();
        $this->organization            = $person->getOrganization();
        $this->organizationPosition    = $person->organization_position;
        $this->organizationManager     = $person->isOrganizationManager();
        $this->timezone                = $person->getTimezone();
        $this->dateCreated             = $person->date_created;
        $this->dateLastLogin           = $person->date_last_login;
        $this->browser                 = $person->browser;
        $this->userGroups              = $person->getPublicUsergroups();
        $this->agentGroups             = $person->getPublicAgentgroups();
        $this->labels                  = $person->getLabels();
        $this->primaryEmail            = $person->getPrimaryEmail();
        $this->ticketsCount            = $person->getTicketsCount();
        $this->chatsCount              = $person->getChatsCount();
        $this->phoneNumbers            = $person->getPhoneNumbersArray();
        $this->fields                  = $person->custom_data;
        $this->contactData             = $person->getContactData();
        $this->emails                  = $person->getEmails();
        $this->teams                   = $person->getTeams();
        $this->primaryTeam             = $person->getPrimaryTeam();
        $this->agentData               = $person->getAgentData();
    }

    /**
     * @param Avatar $avatar
     *
     * @return $this
     */
    public function setAvatar(Avatar $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @param bool $online
     *
     * @return $this
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @param $lastSeen
     *
     * @return $this
     */
    public function setLastSeen($lastSeen = null)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }
}
