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

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\CustomFields\CustomDataCollection;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApiPerson.
 *
 * @JMS\ExclusionPolicy("none")
 */
class ApiPerson
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
    protected $picture_blob;

    /**
     * True if user`s picture disabled.
     *
     * @var bool
     */
    protected $disable_picture;

    /**
     * The URL to the users gravatar if any.
     *
     * @var string
     */
    protected $gravatar_url;

    /**
     * Is this person a contact?
     *
     * @var bool
     */
    protected $is_contact;

    /**
     * Is this person a user?
     *
     * @var bool
     */
    protected $is_user;

    /**
     * Is this person an agent?
     *
     * @var bool
     */
    protected $is_agent;

    /**
     * Was this person an agent?
     *
     * @var bool
     */
    protected $was_agent;

    /**
     * Is person allowed to use agent interface.
     *
     * @var bool
     */
    protected $can_agent;

    /**
     * Is person allowed to use admin interface.
     *
     * @var bool
     */
    protected $can_admin;

    /**
     * Is person allowed to use billing interface.
     *
     * @var bool
     */
    protected $can_billing;

    /**
     * Are autoresponses disabled?
     *
     * @var bool
     */
    protected $disable_autoresponses;

    /**
     * Disabled autoresponses log.
     *
     * @var string
     */
    protected $disable_autoresponses_log;

    /**
     * Does person has confirmed their email?
     *
     * @var bool
     */
    protected $is_confirmed;

    /**
     * Is the user deleted?
     *
     * @var bool
     */
    protected $is_deleted;

    /**
     * Is the user disabled?
     *
     * @var bool
     */
    protected $is_disabled;

    /**
     * The way person was created.
     *
     * @var string
     */
    protected $creation_system;

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $name;

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $first_name;

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $last_name;

    /**
     * The users title prefix (Mr., Mrs., etc).
     *
     * @var string
     */
    protected $title_prefix;

    /**
     * Overrides the display name of an person in the user interface (agents only).
     *
     * @var string
     */
    protected $override_display_name;

    /**
     * The summary field as filled in by agents.
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
     * @var Organization
     */
    protected $organization;

    /**
     * The persons position at the organization.
     *
     * @var string
     */
    protected $organization_position;

    /**
     * True if the person is a manager of their organization.
     *
     * @var bool
     */
    protected $organization_manager;

    /**
     * The timezone associated with this user.
     *
     * @var string
     */
    protected $timezone;

    /**
     * The date the user was inserted into the system.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The date the user was logged in last time.
     *
     * @var \DateTime
     */
    protected $date_last_login;

    /**
     * The browser person was used last time.
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
    protected $usergroups;

    /**
     * Labels associated with this user.
     *
     * @var array
     */
    protected $labels;

    /**
     * @var string
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     */
    protected $primary_email;

    /**
     * @var array
     *
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\PersonEmail>>")
     */
    protected $emails;

    /**
     * @var Avatar
     */
    protected $avatar;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var \DateTime
     */
    protected $last_seen;

    /**
     * @JMS\Type("array<array<string>>")
     *
     * @var array
     */
    protected $phone_numbers;

    /**
     * @var int
     */
    protected $tickets_count;

    /**
     * @var int
     */
    protected $chats_count;

    /**
     * @var CustomDataCollection
     *
     * @JMS\Type("custom_data<array>")
     */
    protected $fields;

    /**
     * @var \Application\DeskPRO\Entity\PersonContactData[]
     *
     * @JMS\Type("collection")
     */
    protected $contact_data;

    /**
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->id                        = $person->getId();
        $this->picture_blob              = $person->picture_blob;
        $this->disable_picture           = $person->disable_picture;
        $this->gravatar_url              = $person->getGravatarUrl();
        $this->is_contact                = $person->is_contact;
        $this->is_user                   = $person->isUser();
        $this->is_agent                  = $person->isAgent();
        $this->was_agent                 = $person->was_agent;
        $this->can_agent                 = $person->can_agent;
        $this->can_admin                 = $person->can_admin;
        $this->can_billing               = $person->getRealCanBilling();
        $this->disable_autoresponses     = $person->disable_autoresponses;
        $this->disable_autoresponses_log = $person->disable_autoresponses_log;
        $this->is_confirmed              = $person->isConfirmed();
        $this->is_deleted                = $person->isDeleted();
        $this->is_disabled               = $person->isDisabled();
        $this->creation_system           = $person->creation_system;
        $this->name                      = $person->name;
        $this->first_name                = $person->first_name;
        $this->last_name                 = $person->last_name;
        $this->title_prefix              = $person->title_prefix;
        $this->override_display_name     = $person->override_display_name;
        $this->summary                   = $person->summary;
        $this->language                  = $person->getLanguage();
        $this->organization              = $person->getOrganization();
        $this->organization_position     = $person->organization_position;
        $this->organization_manager      = $person->isOrganizationManager();
        $this->timezone                  = $person->getTimezone();
        $this->date_created              = $person->date_created;
        $this->date_last_login           = $person->date_last_login;
        $this->browser                   = $person->browser;
        $this->usergroups                = $person->getUsergroups();
        $this->labels                    = $person->getLabelsArray();
        $this->primary_email             = $person->getPrimaryEmail();
        $this->tickets_count             = $person->getTicketsCount();
        $this->chats_count               = $person->getChatsCount();
        $this->phone_numbers             = $person->getPhoneNumbersArray();
        $this->fields                    = $person->custom_data;
        $this->contact_data              = $person->getContactData();
        $this->emails                    = $person->getEmails();
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
     * @param \DateTime $last_seen
     *
     * @return $this
     */
    public function setLastSeen(\DateTime $last_seen = null)
    {
        $this->last_seen = $last_seen;

        return $this;
    }
}
