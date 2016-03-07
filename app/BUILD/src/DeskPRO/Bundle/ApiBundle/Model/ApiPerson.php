<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Model;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\CustomFields\CustomDataCollection;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApiPerson.
 *
 * @JMS\ExclusionPolicy("all")
 */
class ApiPerson
{
    /**
     * @var AvatarResolver
     */
    protected $avatar_resolver;

    /**
     * @var AgentDataService
     */
    protected $agent_data_service;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @param AvatarResolver   $avatar_resolver
     * @param AgentDataService $agent_data_service
     * @param Person           $person
     */
    public function __construct(Person $person, AvatarResolver $avatar_resolver, AgentDataService $agent_data_service)
    {
        $this->avatar_resolver    = $avatar_resolver;
        $this->agent_data_service = $agent_data_service;
        $this->person             = $person;
    }

    /**
     * The unique ID.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     * @JMS\SerializedName("id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->person->getId();
    }

    /**
     * The user`s profile picture.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Blob>")
     * @JMS\SerializedName("picture_blob")
     *
     * @return Blob
     */
    public function getPictureBlob()
    {
        return $this->person->picture_blob;
    }

    /**
     * The user`s profile picture.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("disable_picture")
     *
     * @return bool
     */
    public function isDisablePicture()
    {
        return $this->person->disable_picture;
    }

    /**
     * The URL to the users gravatar if any.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("gravatar_url")
     *
     * @return string
     */
    public function getGravatarUrl()
    {
        return $this->person->getGravatarUrl();
    }

    /**
     * Is this person a contact?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_contact")
     *
     * @return bool
     */
    public function isContact()
    {
        return $this->person->isContact();
    }

    /**
     * Is this person a user?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_user")
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->person->isUser();
    }

    /**
     * Is this person an agent?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_agent")
     *
     * @return bool
     */
    public function isAgent()
    {
        return $this->person->isAgent();
    }

    /**
     * Was this person an agent?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("was_agent")
     *
     * @return bool
     */
    public function wasAgent()
    {
        return $this->person->was_agent;
    }

    /**
     * Is person allowed to use agent interface.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("can_agent")
     *
     * @return bool
     */
    public function canAgent()
    {
        return $this->person->can_agent;
    }

    /**
     * Is person allowed to use admin interface.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("can_admin")
     *
     * @return bool
     */
    public function canAdmin()
    {
        return $this->person->can_admin;
    }

    /**
     * Is person allowed to use billing interface.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("can_bolling")
     *
     * @return bool
     */
    public function canBilling()
    {
        return $this->person->can_billing;
    }

    /**
     * Are autoresponses disabled?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("disable_autoresponses")
     *
     * @return bool
     */
    public function areAutoresponsesDisabled()
    {
        return $this->person->disable_autoresponses;
    }

    /**
     * Disabled autoresponses log.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("disable_autoresponses_log")
     *
     * @return bool
     */
    public function getDisableAutoresponsesLog()
    {
        return $this->person->disable_autoresponses_log;
    }

    /**
     * Does person has confirmed their email?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_confirmed")
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->person->isConfirmed();
    }

    /**
     * Is the user deleted?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_deleted")
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->person->isDeleted();
    }

    /**
     * Is the user disabled?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_disabled")
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->person->isDisabled();
    }

    /**
     * The way person was created.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("creation_system")
     *
     * @return string
     */
    public function getCreationSystem()
    {
        return $this->person->creation_system;
    }

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("name")
     *
     * @return string
     */
    public function getName()
    {
        return $this->person->name;
    }

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("first_name")
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->person->first_name;
    }

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("last_name")
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->person->last_name;
    }

    /**
     * The users title prefix (Mr., Mrs., etc).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("title_prefix")
     *
     * @return string
     */
    public function getTitlePrefix()
    {
        return $this->person->title_prefix;
    }

    /**
     * Overrides the display name of an person in the user interface (agents only).
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("override_display_name")
     *
     * @return string
     */
    public function getOverrideDisplayName()
    {
        return $this->person->override_display_name;
    }

    /**
     * The summary field as filled in by agents.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("summary")
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->person->summary;
    }

    /**
     * The summary field as filled in by agents.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     * @JMS\SerializedName("language")
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->person->getLanguage();
    }

    /**
     * The users organization.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Organization>")
     * @JMS\SerializedName("organization")
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->person->getOrganization();
    }

    /**
     * The persons position at the organization.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("organization_position")
     *
     * @return string
     */
    public function getOrganizationPosition()
    {
        return $this->person->organization_position;
    }

    /**
     * True if the person is a manager of their organization.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("organization_position")
     *
     * @return bool
     */
    public function isOrganizationManager()
    {
        return $this->person->organization_manager;
    }

    /**
     * The timezone associated with this user.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("timezone")
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->person->getTimezone();
    }

    /**
     * The date the user was inserted into the system.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_created")
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->person->date_created;
    }

    /**
     * The date the user was inserted into the system.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("date_last_login")
     *
     * @return \DateTime
     */
    public function getDateLastLogin()
    {
        return $this->person->date_last_login;
    }

    /**
     * The browser person was used last time.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("browser")
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->person->browser;
    }

    /**
     * Usergroups the user belongs to.
     *
     * @JMS\VirtualProperty()
     * @ JMS\Type("ArrayCollection<entity<Application\DeskPRO\Entity\Usergroup>>")
     * @JMS\SerializedName("usergroups")
     *
     * @return ArrayCollection
     */
    public function getUsergroups()
    {
        return $this->person->getUsergroups();
    }

    /**
     * Labels associated with this user.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("labels")
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->person->getLabelsArray();
    }

    /**
     * Get the primary email address, or null if this person has none.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("primary_emails")
     *
     * @return string
     */
    public function getPrimaryEmail()
    {
        return $this->person->getPrimaryEmailAddress();
    }

    /**
     * Get the primary email address, or null if this person has none.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("emails")
     *
     * @return array
     */
    public function getEmails()
    {
        return ListUtils::filterMap($this->person->getEmails(), function ($email) {
            /* @var \Application\DeskPRO\Entity\PersonEmail $email */
            return $email->getEmail();
        });
    }

    /**
     * Get user`s avatar model.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     * @JMS\SerializedName("avatar")
     *
     * @return Avatar
     */
    public function getAvatar()
    {
        return $this->avatar_resolver->getAvatarModel($this->person);
    }

    /**
     * Is the agent online?
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("boolean")
     * @JMS\SerializedName("online")
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->agent_data_service->isAgentOnline($this->person);
    }

    /**
     * Get the DateTime when the agent was last seen online.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("last_seen")
     *
     * @return \DateTime
     */
    public function getLastSeen()
    {
        $last_seen = $this->agent_data_service->getLastSeen($this->person);
        if ($last_seen) {
            $last_seen = new \DateTime($last_seen);
        }

        return $last_seen ?: null;
    }

    /**
     * Get person`s phones.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<array<string>>")
     * @JMS\SerializedName("phone_numbers")
     *
     * @return array
     */
    public function getPhoneNumbersArray()
    {
        return $this->person->getPhoneNumbersArray();
    }

    /**
     * Count of tickets person was assigned.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     * @JMS\SerializedName("tickets_count")
     *
     * @return int
     */
    public function getTicketsCount()
    {
        return $this->person->getTicketsCount();
    }

    /**
     * Count of tickets person participating.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("integer")
     * @JMS\SerializedName("chats_count")
     *
     * @return int
     */
    public function getChatsCount()
    {
        return $this->person->getChatsCount();
    }

    /**
     * Custom person`s field.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\CustomFields\CustomDataCollection")
     * @JMS\SerializedName("fields")
     *
     * @return CustomDataCollection
     */
    public function getCustomData()
    {
        return new CustomDataCollection($this->person->custom_data);
    }
}
