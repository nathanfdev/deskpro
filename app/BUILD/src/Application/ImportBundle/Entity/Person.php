<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use DateTime;
use DateTimeZone;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting person entity.
 *
 * Class Person
 */
final class Person extends AbstractEntity implements LabelAwareInterface, LanguageAwareInterface
{
    const INITIAL_PASSWORD = 'password';

    const PASSWORD_SCHEME_PLAIN  = 'plain';
    const PASSWORD_SCHEME_BCRYPT = 'bcrypt';

    /**
     * @var bool
     */
    private $is_agent = false;

    /**
     * @var bool
     */
    private $is_user = false;

    /**
     * @var bool
     */
    private $is_admin = false;

    /**
     * @var bool
     */
    private $is_disabled = false;

    /**
     * @var bool
     */
    private $is_deleted = false;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * @var string
     */
    private $name;

    /**
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
     * @var DateTimeZone
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
     * @var ContactData[]
     */
    private $contact_data;

    /**
     * @var Collection
     */
    private $custom_fields;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contact_data  = new Collection();
        $this->custom_fields = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PERSON;
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
        return $this->is_user || $this->is_agent || $this->is_admin;
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
        if ($this->first_name) {
            return $this->first_name;
        }

        $name = $this->getName();
        if ($name) {
            $names = explode(' ', $name);

            if (count($names) > 1) {
                array_pop($names);

                return implode(' ', $names);
            } else {
                return $name;
            }
        }

        return;
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
        if ($this->last_name) {
            return $this->last_name;
        }

        $name = $this->getName();
        if ($name) {
            $names = explode(' ', $name);

            if (count($names) > 1) {
                return array_pop($names);
            }
        }

        return;
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
        if ($this->name) {
            return $this->name;
        }
        if ($this->getFirstEmail()) {
            $email = @explode('@', $this->getFirstEmail(), 2);
            if (isset($email[0])) {
                return $email[0];
            }
        }

        return;
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
     * Returns true if the person password scheme is plain.
     *
     * @return bool
     */
    public function isPlainPasswordScheme()
    {
        return $this->password_scheme === self::PASSWORD_SCHEME_PLAIN;
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
        return $this->timezone ? $this->timezone->getName() : 'UTC';
    }

    /**
     * Set time zone.
     *
     * @param DateTimeZone|null $timezone
     *
     * @return $this
     */
    public function setTimezone(DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Returns date created.
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date created.
     *
     * @param DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;

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
        return $this->emails;
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
        if (!in_array($email, $this->emails)) {
            $this->emails[] = $email;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;

        return $this;
    }

    /**
     * Returns a collection of person user groups.
     *
     * @return array
     */
    public function getUserGroups()
    {
        return $this->user_groups;
    }

    /**
     * Add an user group.
     *
     * @param string $user_group
     *
     * @return $this
     */
    public function addUserGroup($user_group)
    {
        $this->user_groups[] = $user_group;

        return $this;
    }

    /**
     * Returns person contact data.
     *
     * @return Collection|ContactData[]
     */
    public function getContactData()
    {
        return $this->contact_data;
    }

    /**
     * Add an person contact data.
     *
     * @param ContactData $contact
     *
     * @return $this
     */
    public function addContact(ContactData $contact)
    {
        $this->contact_data->attach($contact);

        return $this;
    }

    /**
     * Returns a collection of person custom fields.
     *
     * @return Collection
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * Add a custom field.
     *
     * @param CustomField $custom_field
     *
     * @return $this
     */
    public function addCustomField(CustomField $custom_field)
    {
        $this->custom_fields->attach($custom_field);

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
            'import_map_key'        => $this->import_map_key,
            'is_agent'              => $this->is_agent,
            'is_user'               => $this->is_user,
            'is_admin'              => $this->is_admin,
            'is_disabled'           => $this->is_disabled,
            'is_deleted'            => $this->is_deleted,
            'first_name'            => $this->first_name,
            'last_name'             => $this->last_name,
            'name'                  => $this->name,
            'override_display_name' => $this->override_display_name,
            'password'              => $this->password,
            'password_scheme'       => $this->password_scheme,
            'timezone'              => $this->timezone ? $this->timezone->getName() : null,
            'date_created'          => $this->date_created->format('Y-m-d H:i:s'),
            'language'              => $this->language,
            'organization'          => $this->organization,
            'organization_position' => $this->organization_position,
            'emails'                => $this->emails,
            'labels'                => $this->labels,
            'user_groups'           => $this->user_groups,
            'contact_data'          => $this->contact_data->entitiesToArray(),
            'custom_fields'         => $this->custom_fields->entitiesToArray(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('name', new Constraints\NotBlank())

            ->addPropertyConstraint('date_created', new Constraints\NotBlank())
            ->addPropertyConstraint('date_created', new Constraints\DateTime())

            ->addPropertyConstraint('emails', new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ),
            )))

            ->addPropertyConstraint('user_groups', new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                ),
            )))

            ->addGetterConstraint('firstEmail', new Constraints\NotBlank())
            ->addGetterConstraint('firstEmail', new Constraints\Email())
            ->addGetterConstraint('organizationValid', new Constraints\True())

            ->addPropertyConstraint('contact_data', new Constraints\Valid())
            ->addPropertyConstraint('custom_fields', new Constraints\Valid())
        ;
    }
}
