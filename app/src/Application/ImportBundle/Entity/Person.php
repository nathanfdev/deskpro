<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use DateTime;

/**
 * Exporting person entity
 *
 * Class Person
 * @package Application\ImportBundle\Entity
 */
final class Person extends AbstractEntity
{
    const PASSWORD_SCHEME_PLAIN  = 'plain';
    const PASSWORD_SCHEME_BCRYPT = 'bcrypt';

    /**
     * @var bool
     */
    private $is_agent = false;

    /**
     * Can we merge isAgent and isUser?
     *
     * @var bool
     */
    private $is_user = false;

    /**
     * Can we merge isAgent and isUser and isAdmin?
     *
     * @var bool
     */
    private $is_admin = false;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * Should we use name or just first name?
     *
     * @var string
     */
    private $name;

    /**
     * Too much names, something merge?
     *
     * @var string
     */
    private $override_display_name;

    /**
     * @var string
     */
    private $password;

    /**
     * Available schemes?
     *
     * @var string
     */
    private $password_scheme = self::PASSWORD_SCHEME_PLAIN;

    /**
     * Can we merge it to date created?
     *
     * @var string or int?
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $organization;

    /**
     * @var string
     */
    private $organization_position;

    /**
     * @var array
     */
    private $emails = array();

    /**
     * @var string[]
     */
    private $labels = array();

    /**
     * @var string[]
     */
    private $user_groups = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PERSON;
    }

    /**
     * @return boolean
     */
    public function isAgent()
    {
        return $this->is_agent;
    }

    /**
     * @param boolean $is_agent
     * @return $this
     */
    public function setAsAgent($is_agent)
    {
        $this->is_agent = (bool)$is_agent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUser()
    {
        return $this->is_user;
    }

    /**
     * @param boolean $is_user
     * @return $this
     */
    public function setAsUser($is_user)
    {
        $this->is_user = (bool)$is_user;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * @param boolean $is_admin
     * @return $this
     */
    public function setAsAdmin($is_admin)
    {
        $this->is_admin = (bool)$is_admin;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
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
    public function getOverrideDisplayName()
    {
        return $this->override_display_name;
    }

    /**
     * @param string $override_display_name
     * @return $this
     */
    public function setOverrideDisplayName($override_display_name)
    {
        $this->override_display_name = $override_display_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordScheme()
    {
        return $this->password_scheme;
    }

    /**
     * Returns true if the person password scheme is plain
     *
     * @return bool
     */
    public function isPlainPasswordScheme()
    {
        return $this->password_scheme === self::PASSWORD_SCHEME_PLAIN;
    }

    /**
     * @param string $password_scheme
     * @return $this
     */
    public function setPasswordScheme($password_scheme)
    {
        $this->password_scheme = $password_scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone ? : 'UTC';
    }

    /**
     * @param string $timezone
     * @return $this
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationPosition()
    {
        return $this->organization_position;
    }

    /**
     * @param string $organization_position
     * @return $this
     */
    public function setOrganizationPosition($organization_position)
    {
        $this->organization_position = $organization_position;
        return $this;
    }

    /**
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Returns the first person email
     *
     * @return string|null
     */
    public function getFirstEmail()
    {
        return !empty($this->emails) ? $this->emails[0] : null;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function addEmail($email)
    {
        $this->emails[] = $email;
        return $this;
    }

    /**
     * Returns the person labels
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;
        return $this;
    }

    /**
     * Returns user groups
     *
     * @return array
     */
    public function getUserGroups()
    {
        return $this->user_groups;
    }

    /**
     * @param string $user_group
     * @return $this
     */
    public function addUserGroup($user_group)
    {
        $this->user_groups[] = $user_group;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'                   => $this->oid,
            'is_agent'              => $this->is_agent,
            'is_user'               => $this->is_user,
            'is_admin'              => $this->is_admin,
            'first_name'            => $this->first_name,
            'last_name'             => $this->last_name,
            'name'                  => $this->name,
            'override_display_name' => $this->override_display_name,
            'password'              => $this->password,
            'password_scheme'       => $this->password_scheme,
            'timezone'              => $this->timezone,
            'date_created'          => $this->date_created->format('Y-m-d H:i:s'),
            'language'              => $this->language,
            'organization'          => $this->organization,
            'organization_position' => $this->organization_position,
            'emails'                => $this->emails,
            'labels'                => $this->labels,
            'user_groups'           => $this->user_groups,
            'custom_fields'         => array(), // todo not implemented yet
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('oid', new Constraints\NotBlank())
            ->addPropertyConstraint('name', new Constraints\NotBlank())

            ->addPropertyConstraint('date_created', new Constraints\NotBlank())
            ->addPropertyConstraint('date_created', new Constraints\DateTime())

            ->addPropertyConstraint('emails', new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ),
            )))

            ->addGetterConstraint('firstEmail', new Constraints\NotBlank())
            ->addGetterConstraint('firstEmail', new Constraints\Email())

        ;
    }
}
