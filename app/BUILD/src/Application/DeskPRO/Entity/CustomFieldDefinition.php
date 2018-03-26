<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Hierarchy\Hierarchical;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A custom field definition.
 *
 * @property int $id
 * @property CustomFieldDefinition $parent
 * @property ArrayCollection $children
 */
class CustomFieldDefinition extends DomainObject implements HasPhraseName, Hierarchical
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Is the field associated with an app?
     * These generally cant be edited.
     *
     * @var \Application\DeskPRO\Entity\AppInstance
     */
    protected $app;

    /**
     * JS class to init
     * todo remove unused?
     *
     * @var string
     */
    protected $js_class = '';

    /**
     * True if this field uses a custom template when rendering the form input
     * todo remove unused?
     *
     * @var string
     */
    protected $has_form_template = false;

    /**
     * True i this field uses a custom template when rendering the form value for display
     * todo remove unused?
     *
     * @var string
     */
    protected $has_display_template = false;

    /**
     * The title.
     *
     * @var string
     */
    protected $title;

    /**
     * The description.
     *
     * @var string
     */
    protected $description;

    /**
     * Options for the field.
     */
    protected $options;

    /**
     * Can the field be viewed by the user?
     *
     * @var string
     */
    protected $is_user_enabled;

    /**
     * @var string
     */
    protected $is_enabled;

    /**
     * @var int
     */
    protected $display_order;

    /**
     * @var string
     */
    protected $default_value;

    /**
     * @var string
     */
    protected $is_agent_field;

    /**
     * @var CustomFieldDefinition
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $children;

    /**
     * the class of Form Type.
     *
     * @var string
     */
    protected $form_type;

    /**
     * the classname of the owner of the custom field (Ticket, Person, etc).
     *
     * @var string
     */
    protected $owner_class;

    /**
     * @var string
     */
    protected $context_class;

    /**
     * @var int
     */
    protected $context_id;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children        = new ArrayCollection();
        $this->description     = '';
        $this->display_order   = 0;
        $this->options         = [];
        $this->is_enabled      = true;
        $this->is_user_enabled = true;
        $this->is_agent_field  = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     *
     * @return CustomFieldDefinition
     */
    public function spawnChild($title)
    {
        $new                  = new self();
        $new->parent          = $this;
        $new->title           = $title;
        $new->form_type       = $this->form_type;
        $new->owner_class     = $this->owner_class;
        $new->context_class   = $this->context_class;
        $new->is_enabled      = $this->is_enabled;
        $new->is_user_enabled = $this->is_user_enabled;
        $new->is_agent_field  = $this->is_agent_field;
        $new->app             = $this->app;

        return $new;
    }

    /**
     * @return ArrayCollection|CustomFieldDefinition[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int $defId
     *
     * @return CustomFieldDefinition
     */
    public function getChildById($defId)
    {
        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('id', $defId));

        return $this->children->matching($criteria)->first();
    }

    /**
     * @param mixed $contextEntity
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getChoices($contextEntity)
    {
        if (!$contextEntity || !$contextEntity->getId()) {
            return new ArrayCollection();
        }

        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq('context_id', $contextEntity->getId()));
        $criteria->andWhere($criteria->expr()->eq('context_class', ClassUtils::getClass($contextEntity)));

        return $this->children->matching($criteria);
    }

    /**
     * @param CustomFieldDefinition $child
     */
    public function addChild(CustomFieldDefinition $child)
    {
        $this->children->add($child);
    }

    /**
     * @return bool
     */
    public function isForOrganization()
    {
        return $this->context_class == Organization::class;
    }

    /**
     * @return bool
     */
    public function isForPerson()
    {
        return $this->context_class == Person::class;
    }

    /**
     * @param mixed $entity
     *
     * @return $this
     */
    public function setContext($entity)
    {
        if ($entity) {
            $this->setModelField('context_class', ClassUtils::getClass($entity));
            $this->setModelField('context_id', $entity->getId());
        } else {
            $this->setModelField('context_class', '');
            $this->setModelField('context_id', null);
        }

        return $this;
    }

    /**
     * @return bool|string
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $agent_interface
     *
     * @return bool
     */
    public function isRequired($agent_interface = false)
    {
        // never required if agent is filling it out
        if ($agent_interface) {
            return false;
        }

        return (bool) $this->getOption('required', false);
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return (bool) $this->getOption('multiple', false);
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        return (bool) $this->getOption('expanded', false);
    }

    /**
     * @return bool
     */
    public function isOptionsEditableByUser()
    {
        return (bool) $this->getOption('allow_edit', false);
    }

    /**
     * @return \Application\DeskPRO\Form\Type\CustomFields\CustomFieldType
     */
    public function createType()
    {
        $class = $this->form_type;

        return new $class($this);
    }

    /**
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Form\Type\CustomFields\Definitions\CustomFieldDefinitionType
     */
    public function createDefinitionType()
    {
        if (!$this->form_type) {
            throw new \Exception('form_type must be specified');
        }

        $type  = substr($this->form_type, strrpos($this->form_type, '\\') + 1);
        $class = 'Application\DeskPRO\Form\Type\CustomFields\Definitions\\'.str_replace('Type', 'DefinitionType', $type);

        return new $class();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return App::getTranslator()->getPhraseObject($this, 'title');
    }

    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return App::getTranslator()->getPhraseObject($this, 'description');
    }

    /**
     * @return string
     */
    public function getRealDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }

        $name = strtolower(\Orb\Util\Util::getBaseClassname($this));

        $phrase_name = 'obj_'.$name.'.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        if ($property == 'description') {
            return $this->description;
        }

        return $this->title;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable([
            'name'    => 'custom_field_definition',
            'indexes' => [
                'context_idx' => [
                    'columns' => [
                        'context_class',
                        'context_id',
                    ],
                ],
            ],
        ]);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomFieldDefinition';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);

        // todo is these columns required?
        $metadata->mapField([
            'fieldName'  => 'js_class',
            'type'       => 'string',
            'nullable'   => false,
            'columnName' => 'js_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'has_form_template',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'has_form_template',
        ]);
        $metadata->mapField([
            'fieldName'  => 'has_display_template',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'has_display_template',
        ]);

        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'description',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'description',
        ]);
        $metadata->mapField([
            'fieldName'  => 'options',
            'type'       => 'array',
            'nullable'   => false,
            'columnName' => 'options',
        ]);
        $metadata->mapField([
            'fieldName'  => 'default_value',
            'type'       => 'string',
            'length'     => 500,
            'nullable'   => true,
            'columnName' => 'default_value',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);

        $metadata->mapField([
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'is_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_user_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'is_user_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_agent_field',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'is_agent_field',
        ]);

        $metadata->mapField([
            'fieldName'  => 'form_type',
            'type'       => 'string',
            'nullable'   => false,
            'columnName' => 'form_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'owner_class',
            'type'       => 'string',
            'nullable'   => false,
            'columnName' => 'owner_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'context_class',
            'type'       => 'string',
            'nullable'   => true,
            'columnName' => 'context_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'context_id',
            'type'       => 'integer',
            'nullable'   => true,
            'columnName' => 'context_id',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'parent',
            'targetEntity' => self::class,
            'mappedBy'     => null,
            'inversedBy'   => 'children',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'parent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->mapOneToMany([
            'fieldName'    => 'children',
            'targetEntity' => self::class,
            'fetch'        => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            'cascade'      => [
                'remove',
                'persist',
                'merge',
            ],
            'mappedBy' => 'parent',
            'orderBy'  => [
                'display_order' => 'ASC',
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'app',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'app_id',
                    'referencedColumnName' => 'id',
                    'unique'               => false,
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getOwnerClass()
    {
        return $this->owner_class;
    }

    /**
     * @return string
     */
    public function getContextClass()
    {
        return $this->context_class;
    }

    /**
     * @return int
     */
    public function getContextId()
    {
        return $this->context_id;
    }

    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }
}
