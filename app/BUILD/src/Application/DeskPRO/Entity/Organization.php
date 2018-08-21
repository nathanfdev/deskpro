<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Avatar\AvatarOwner;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use FOS\ElasticaBundle\Transformer\HighlightableModelInterface;
use Orb\Util\Numbers;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An organization is a grouping we put similar people into (eg companies).
 */
class Organization extends DomainObject implements HighlightableModelInterface, AvatarOwner, Entity\Labels\LabelsOwner, Entity\Hierarchy\Hierarchical
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The org picture.
     *
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $picture_blob = null;

    /**
     * The organization name.
     *
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $name = '';

    /**
     * The summary field as filled in by agents.
     *
     * @var string
     */
    protected $summary = '';

    /**
     * The org importance.
     *
     * @var int
     *
     * @Assert\NotNull()
     */
    protected $importance = 0;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|CustomDataAbstract[]
     */
    protected $custom_data;

    /**
     * Usergroups the user belongs to.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $usergroups;

    /**
     * Users who are set to automatically be added to tickets and other org things.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $auto_cc_people;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection(property={"label"})
     */
    protected $labels;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     */
    protected $contact_data;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @Assert\Valid()
     * @AppAssert\UniqueCollection()
     */
    protected $email_domains;

    /**
     * The date the org was inserted into the system.
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $slas;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $twitter_users;

    /**
     * @var Person[]|ArrayCollection
     */
    protected $members;

    /**
     * @var Organization|null
     */
    protected $parent;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var null|\Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     * The search result highlights.
     *
     * @var array
     */
    protected $_search_highlights;

    /**
     * @var Ticket[]|ArrayCollection
     */
    protected $tickets;

    /**
     * @var OrganizationPhoneNumber[]
     */
    protected $phone_numbers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('custom_data', new ArrayCollection());
        $this->setModelField('labels', new ArrayCollection());
        $this->setModelField('contact_data', new ArrayCollection());
        $this->setModelField('usergroups', new ArrayCollection());
        $this->setModelField('twitter_users', new ArrayCollection());

        $this->setModelField('date_created', new \DateTime());

        $this->slas          = new ArrayCollection();
        $this->children      = new ArrayCollection();
        $this->members       = new ArrayCollection();
        $this->tickets       = new ArrayCollection();
        $this->email_domains = new ArrayCollection();
        $this->phone_numbers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the organization name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the organization name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $old        = $this->name;
        $this->name = (string) $name;
        $this->_onPropertyChanged('name', $old, $this->name);

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->setModelField('summary', $summary);

        return $this;
    }

    /**
     * Set the default importance of people in this org.
     *
     * @param int $importance
     *
     * @return $this
     */
    public function setImportance($importance)
    {
        $old              = $this->importance;
        $this->importance = Numbers::bound($importance, 0, 5);
        $this->_onPropertyChanged('importance', $old, $this->importance);

        return $this;
    }

    /**
     * @return int
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int|CustomDefOrganization $field_id
     *
     * @return CustomDataOrganization
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefOrganization) {
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
     * @return CustomDataAbstract[]|ArrayCollection
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * Add contact data.
     *
     * @param OrganizationContactData $contact_data
     */
    public function addContactData(OrganizationContactData $contact_data)
    {
        $this['contact_data']->add($contact_data);
        $contact_data['organization'] = $this;

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
     * @return Entity\OrganizationContactData[]|ArrayCollection
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

    public function removeCustomDataForField(CustomDefOrganization $field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $this->custom_data->removeElement($data);
                $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
            }
        }
    }

    /**
     * @param Collection $custom_data
     */
    public function setCustomData(Collection $custom_data)
    {
        foreach ($custom_data as $cd) {
            $cd->organization = $this;
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Set custom field data for a particular field.
     *
     * @param int   $field_id
     * @param mixed $value_type
     * @param mixed $value
     *
     * @throws \Exception
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

            $field = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($field_id);
            if (!$field) {
                throw new \Exception("Invalid field_id `$field_id`");
            }
            $custom_data          = new CustomDataOrganization();
            $custom_data['field'] = $field;
        }

        if ($value === null) {
            $this['custom_data']->removeElement($custom_data);

            return;
        }

        $custom_data[$value_type] = $value;

        if ($is_new) {
            $this->addCustomData($custom_data);
        }

        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);

        return $custom_data;
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
     * Add a custom data item to this ticket.
     *
     * @param CustomDataOrganization $data
     */
    public function addCustomData(CustomDataOrganization $data)
    {
        if ($this->custom_data === null) {
            $this->custom_data = new ArrayCollection();
        }

        $this->custom_data->add($data);
        $data['organization'] = $this;
        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
    }

    /**
     * Render a custom field.
     */
    public function renderCustomField($field_id, $context = 'html')
    {
        $f_def = App::getEntityRepository('DeskPRO:CustomDefOrganization')->find($field_id);

        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, [$f_def]);

        $value    = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
        $rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

        return $rendered;
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

        $org_field_defs      = App::getApi('custom_fields.organizations')->getEnabledFields();
        $org_data_structured = App::getApi('custom_fields.util')->createDataHierarchy([$data], $org_field_defs);

        $custom_fields = App::getApi('custom_fields.organizations')->getFieldsDisplayArray(
            $org_field_defs,
            $org_data_structured
        );

        $custom_fields = array_pop($custom_fields);

        return $custom_fields;
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
        return $this->usergroups->filter(
            function (Usergroup $group) {
                return !$group->is_agent_group && $group->sys_name !== 'everyone' && $group->is_enabled;
            }
        );
    }

    /**
     * @param Usergroup $userGroup
     */
    public function addUserGroup(Usergroup $userGroup)
    {
        $this->usergroups->add($userGroup);
        $this->_onPropertyChanged('usergroups', $this->usergroups, $this->usergroups);
    }

    /**
     * @return Organization|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Organization $parent = null)
    {
        $oldParent = $this->parent;
        $this->setModelField('parent', $parent);
        $this->_onPropertyChanged('parent', $oldParent, $parent);

        return $this;
    }

    /**
     * Set organization picture.
     *
     * @param Blob|null $blob
     *
     * @return $this
     */
    public function setPicture(Blob $blob = null)
    {
        $this->setModelField('picture_blob', $blob);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getPicture()
    {
        return $this->picture_blob;
    }

    /**
     * Gets the URL to a picture for the org. If there is no picture for the org, a default one
     * will be rendered. Use hasPicture if you need to know if a picture exists.
     *
     * @deprecated Use AvatarResolver
     *
     * @return null|string
     */
    public function getPictureUrl($size = 80, $secure = null)
    {
        // Null means detect
        if ($secure === null and App::isWebRequest()) {
            $request = App::getRequest();
            if ($request->isSecure()) {
                $secure = true;
            }
        }

        $url = false;
        if ($this->picture_blob) {
            $url = App::get('router.default')->generate(
                'serve_blob_sizefit',
                [
                    'blob_auth_id' => $this->picture_blob->getAuthId(),
                    'filename'     => $this->picture_blob->getFilenameSafe(),
                    's'            => $size,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if (!$url) {
            $url = App::get('router.default')->generate(
                'serve_org_picture_default',
                [
                    's'        => $size,
                    'size-fit' => 1,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if ($secure) {
            $url = preg_replace('#^http:#', 'https:', $url);
        }

        return $url;
    }

    /**
     * Cehck if the company has a picture uploaded.
     *
     * @return bool
     */
    public function hasPicture()
    {
        if ($this->picture_blob) {
            return true;
        }

        return false;
    }

    public function hasSla(Sla $sla)
    {
        foreach ($this->slas as $org_sla) {
            if ($org_sla->id == $sla->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset labels.
     *
     * @return $this
     */
    public function clearLabels()
    {
        foreach ($this->labels as $data) {
            $this->labels->removeElement($data);
        }

        $this->_onPropertyChanged('labels', null, $this->labels);

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
    public function addLabel(Entity\Labels\Label $label)
    {
        $label['organization'] = $this;
        $this->labels->add($label);
        $this->_onPropertyChanged('labels', $this->labels, $this->labels);
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(Entity\Labels\Label $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
            $this->_onPropertyChanged('labels', null, $this->labels);
        }
    }

    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelOrganization');
        }

        return $this->_label_manager;
    }

    /**
     * @param OrganizationEmailDomain $emailDomain
     *
     * @return $this
     */
    public function addEmailDomain(OrganizationEmailDomain $emailDomain)
    {
        $emailDomain['organization'] = $this;
        $this->email_domains->add($emailDomain);
        $this->_onPropertyChanged('email_domains', $this->email_domains, $this->email_domains);

        return $this;
    }

    /**
     * @param OrganizationEmailDomain $email_domain
     */
    public function removeEmailDomain(OrganizationEmailDomain $email_domain)
    {
        if ($this->email_domains->contains($email_domain)) {
            $this->email_domains->removeElement($email_domain);
            $this->_onPropertyChanged('email_domains', null, $this->email_domains);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getEmailDomains()
    {
        return $this->email_domains;
    }

    /**
     * @param OrganizationEmailDomain|string $emailDomain
     *
     * @return bool
     */
    public function hasEmailDomain($emailDomain)
    {
        if ($emailDomain instanceof OrganizationEmailDomain) {
            $emailDomain = $emailDomain->getDomain();
        }

        return $this->email_domains->filter(function (OrganizationEmailDomain $existingDomain) use ($emailDomain) {
            return $existingDomain->getDomain() === $emailDomain;
        })->count() > 0;
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

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        if ($deep) {
            $data['labels'] = [];
            foreach ($this->labels as $label) {
                $data['labels'][] = $label['label'];
            }

            $data['email_domains'] = [];
            foreach ($this->email_domains as $domain) {
                $data['email_domains'][] = $domain->domain;
            }

            $data['member_count'] = App::getEntityRepository('DeskPRO:Organization')->countMembersFor($this);
        }

        $data['picture_url'] = $this->getPictureUrl();

        $field_manager = App::getContainer()->getSystemService('org_fields_manager');
        $field_manager->addApiData($this, $data);

        return $data;
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
     * @return Blob
     */
    public function getAvatarBlob()
    {
        return $this->picture_blob;
    }

    /**
     * @return int
     */
    public function getMembersCount()
    {
        return $this->members->count();
    }

    /**
     * @return Person[]|ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function addMember(Person $person)
    {
        $person->setOrganization($this);
        $this->members->add($person);

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function removeMember(Person $person)
    {
        if ($this->members->contains($person)) {
            $person->setOrganization(null);
            $this->members->removeElement($person);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketsCount()
    {
        return $this->tickets->count();
    }

    /**
     * @return OrganizationPhoneNumber[]|ArrayCollection
     */
    public function getPhoneNumbers()
    {
        return $this->phone_numbers;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Organization';
        $metadata->setPrimaryTable(['name' => 'organizations']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
                'fieldName'  => 'name',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'name',
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
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'picture_blob',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'picture_blob_id',
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
                'fieldName'     => 'custom_data',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\CustomDataOrganization',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'organization',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'usergroups',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
                'indexBy'      => 'id',
                'joinTable'    => [
                    'name'        => 'organization2usergroups',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'organization_id',
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
        $metadata->mapManyToMany(
            [
                'fieldName'    => 'auto_cc_people',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'joinTable'    => [
                    'name'        => 'organizations_auto_cc',
                    'schema'      => null,
                    'joinColumns' => [
                        0 => [
                            'name'                 => 'organization_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                    'inverseJoinColumns' => [
                        0 => [
                            'name'                 => 'person_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                            'onDelete'             => 'cascade',
                            'columnDefinition'     => null,
                        ],
                    ],
                ],
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'labels',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\LabelOrganization',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'organization',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'contact_data',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\OrganizationContactData',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'organization',
                'orphanRemoval' => true,
                'indexBy'       => 'id',
                'dpApi'         => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'email_domains',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\OrganizationEmailDomain',
                'cascade'       => [0 => 'remove', 1 => 'persist', 3 => 'merge'],
                'mappedBy'      => 'organization',
                'orphanRemoval' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'twitter_users',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\OrganizationTwitterUser',
                'mappedBy'     => 'organization',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'inversedBy'   => 'children',
                'joinColumns'  => [
                    [
                        'name'                 => 'parent_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'children',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'mappedBy'     => 'parent',
                'dpApi'        => true,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'members',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => 'organization',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'tickets',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'mappedBy'     => 'organization',
                'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'     => 'phone_numbers',
                'targetEntity'  => OrganizationPhoneNumber::class,
                'mappedBy'      => 'organization',
                'cascade'       => ['persist', 'detach'],
                'orphanRemoval' => true,
            ]
        );
    }
}
