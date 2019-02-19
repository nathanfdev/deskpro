<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\Basic;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom organization data.
 */
class CustomDataOrganization extends CustomDataAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefOrganization
     */
    protected $field;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefOrganization
     */
    protected $root_field;

    public function setOrganization(Organization $organization)
    {
        $this->setModelField('organization', $organization);

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    public function getOrganizationId()
    {
        return $this->organization['id'];
    }

    /**
     * Set a field.
     *
     * @param CustomDefOrganization $field
     *
     * @return $this
     */
    public function setField(CustomDefOrganization $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefOrganization $field
     *
     * @return $this
     */
    public function setRootField(CustomDefOrganization $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return Organization
     */
    public function getOwner()
    {
        return $this->organization;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = Basic::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_organizations',
                'uniqueConstraints' => [],
            ]
        );
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
                'fieldName'  => 'value',
                'type'       => 'bigint',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'organization',
                'targetEntity' => Organization::class,
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'organization_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'field',
                'targetEntity' => CustomDefOrganization::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'root_field',
                'targetEntity' => CustomDefOrganization::class,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
