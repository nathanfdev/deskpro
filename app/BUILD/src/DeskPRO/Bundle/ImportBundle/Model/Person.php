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

namespace DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting person entity.
 *
 * Class Person
 */
class Person implements LabelAwareModelInterface, LanguageAwareInterface, CustomDataAwareModelInterface, PrimaryImportModelInterface, UsergroupAwareModelInterface, ContactDataAwareModelInterface
{
    use PrimaryImportModelTrait, LabelAwareTrait, CustomDataAwareTrait;

    const PASSWORD_SCHEME_PLAIN  = 'plain';
    const PASSWORD_SCHEME_BCRYPT = 'bcrypt';

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_agent = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_admin = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_disabled = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_deleted = false;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $first_name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $last_name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $titlePrefix;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $override_display_name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $password;

    /**
     * Available schemes?
     *
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Choice(choices={"plain", "bcrypt"})
     */
    private $password_scheme;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $timezone;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $dateCreated;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $language;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $organization;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $organization_position;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\Count(min="1")
     * @Assert\All(constraints={
     *   @Assert\NotBlank(),
     *   @Assert\Email(strict="true")
     * })
     */
    private $emails = [];

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $userGroups = [];

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $agentGroups = [];

    /**
     * @var ContactData
     *
     * @JMS\Type("DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData")
     *
     * @Assert\Valid()
     */
    private $contact_data;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contact_data = new ContactData();
    }

    /**
     * Is agent?
     *
     * @return bool
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * Set person as agent.
     *
     * @param bool $is_agent
     *
     * @return $this
     */
    public function setAsAgent($is_agent)
    {
        $this->is_agent = (bool) $is_agent;

        return $this;
    }

    /**
     * Does person has login credentials?
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->password || $this->is_agent || $this->is_admin;
    }

    /**
     * Mark person as user
     * If password is empty then initial password will be set up.
     *
     * @param bool $is_user
     *
     * @return $this
     */
    public function setAsUser($is_user)
    {
        $this->is_user = (bool) $is_user;

        return $this;
    }

    /**
     * Is admin?
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Mark person as admin.
     *
     * @param bool $is_admin
     *
     * @return $this
     */
    public function setAsAdmin($is_admin)
    {
        $this->is_admin = (bool) $is_admin;
        if ($this->is_admin) {
            $this->is_agent = true;
        }

        return $this;
    }

    /**
     * Is disabled?
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * Mark person as disabled.
     *
     * @param bool $is_disabled
     *
     * @return $this
     */
    public function setAsDisabled($is_disabled)
    {
        $this->is_disabled = (bool) $is_disabled;

        return $this;
    }

    /**
     * Is deleted?
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * Mark person as deleted.
     *
     * @param bool $is_deleted
     *
     * @return $this
     */
    public function setAsDeleted($is_deleted)
    {
        $this->is_deleted = (bool) $is_deleted;

        return $this;
    }

    /**
     * Returns person first name
     * If property "first_name" is empty then tries to parse person name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set person first name.
     *
     * @param string $first_name
     *
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Returns person last name
     * If property "last_name" is empty then tries to parse person name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set person last name.
     *
     * @param string $last_name
     *
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Returns person name
     * If property "name" is empty tries to get from the first email.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set person name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitlePrefix()
    {
        return $this->titlePrefix;
    }

    /**
     * @param string $titlePrefix
     *
     * @return $this
     */
    public function setTitlePrefix($titlePrefix)
    {
        $this->titlePrefix = $titlePrefix;

        return $this;
    }

    /**
     * Returns custom display name.
     *
     * @return string
     */
    public function getOverrideDisplayName()
    {
        return $this->override_display_name;
    }

    /**
     * Set custom display dame.
     *
     * @param string $override_display_name
     *
     * @return $this
     */
    public function setOverrideDisplayName($override_display_name)
    {
        $this->override_display_name = $override_display_name;

        return $this;
    }

    /**
     * Returns password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returns password scheme
     * Bcrypt by default.
     *
     * @return string
     */
    public function getPasswordScheme()
    {
        return $this->password_scheme;
    }

    /**
     * Set specific password scheme.
     *
     * @param string $password_scheme
     *
     * @return $this
     */
    public function setPasswordScheme($password_scheme)
    {
        $this->password_scheme = $password_scheme;

        return $this;
    }

    /**
     * Returns time zone.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone ?: 'UTC';
    }

    /**
     * Set time zone.
     *
     * @param string $timezone
     *
     * @return $this
     */
    public function setTimezone($timezone = null)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Returns date created.
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Returns a person organization.
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set an organization.
     *
     * @param string $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Returns an organization position.
     *
     * @return string
     */
    public function getOrganizationPosition()
    {
        return $this->organization_position;
    }

    /**
     * Set an organization position.
     *
     * @param string $organization_position
     *
     * @return $this
     */
    public function setOrganizationPosition($organization_position)
    {
        $this->organization_position = $organization_position;

        return $this;
    }

    /**
     * Checking for person's organization info.
     *
     * @Assert\IsTrue()
     *
     * @return bool
     */
    public function isOrganizationValid()
    {
        if ($this->getOrganizationPosition()) {
            if (!$this->getOrganization()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns person emails.
     *
     * @return array
     */
    public function getEmails()
    {
        return array_map('strtolower', $this->emails ?: []);
    }

    /**
     * @param array $emails
     *
     * @return $this
     */
    public function setEmails(array $emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Returns the first person email.
     *
     * @return string|null
     */
    public function getFirstEmail()
    {
        return !empty($this->emails) ? $this->emails[0] : null;
    }

    /**
     * Add an email.
     *
     * @param string $email
     *
     * @return $this
     */
    public function addEmail($email)
    {
        $this->emails[] = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * @param \string[] $userGroups
     *
     * @return $this
     */
    public function setUserGroups(array $userGroups)
    {
        $this->userGroups = $userGroups;

        return $this;
    }

    /**
     * Add a user group.
     *
     * @param string $userGroup
     *
     * @return $this
     */
    public function addUserGroup($userGroup)
    {
        $this->userGroups[] = $userGroup;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getAgentGroups()
    {
        return $this->agentGroups;
    }

    /**
     * @param \string[] $agentGroups
     *
     * @return $this
     */
    public function setAgentGroups(array $agentGroups)
    {
        $this->agentGroups = $agentGroups;

        return $this;
    }

    /**
     * Add an agent group.
     *
     * @param string $agentGroup
     *
     * @return $this
     */
    public function addAgentGroup($agentGroup)
    {
        $this->agentGroups[] = $agentGroup;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContactData()
    {
        return $this->contact_data;
    }
}
