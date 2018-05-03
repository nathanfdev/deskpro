<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomOrganizationFieldDefinitionAlias;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A custom field definition.
 */
class CustomDefOrganization extends CustomDefAbstract
{
    /**
     * Field`s parent.
     *
     * @var CustomDefOrganization
     */
    protected $parent = null;

    /**
     * Field children.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $children = null;

    /**
     * Aliases for this field.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $aliases;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->aliases = new ArrayCollection();
    }

    /**
     * Set parent.
     *
     * @param CustomDefOrganization $parent
     *
     * @return $this
     */
    public function setParent(CustomDefOrganization $parent = null)
    {
        $this->setModelField('parent', $parent);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|CustomOrganizationFieldDefinitionAlias[]|null
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param CustomOrganizationFieldDefinitionAlias $alias
     *
     * @return $this
     */
    public function addAlias(CustomOrganizationFieldDefinitionAlias $alias)
    {
        $alias->setObject($this);
        $this->aliases->add($alias);

        return $this;
    }

    /**
     * @param CustomOrganizationFieldDefinitionAlias $alias
     *
     * @return $this
     */
    public function removeAlias(CustomOrganizationFieldDefinitionAlias $alias)
    {
        $alias->setObject(null);
        $this->aliases->removeElement($alias);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomDefOrganization';
        $metadata->setPrimaryTable(['name' => 'custom_def_organizations']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'js_class',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'js_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'has_form_template',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'has_form_template',
        ]);
        $metadata->mapField([
            'fieldName'  => 'has_display_template',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'has_display_template',
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'description',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'description',
        ]);
        $metadata->mapField([
            'fieldName'  => 'handler_class',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'handler_class',
        ]);
        $metadata->mapField([
            'fieldName'  => 'options',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'options',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_user_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_user_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);
        $metadata->mapField([
            'fieldName'  => 'default_value',
            'type'       => 'string',
            'length'     => 500,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'default_value',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_agent_field',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_agent_field',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefOrganization',
            'mappedBy'     => null,
            'inversedBy'   => 'children',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'parent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'children',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDefOrganization',
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'mappedBy'      => 'parent',
            'orderBy'       => ['display_order' => 'ASC'],
            'orphanRemoval' => true,
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'aliases',
            'targetEntity' => CustomOrganizationFieldDefinitionAlias::class,
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'mappedBy'      => 'object',
            'orphanRemoval' => true,
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
}
