<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Labels on organizations.
 */
class LabelOrganization extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'organizations';

    /**
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $organization;

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->setModelField('organization', $organization);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelOrganization';
        $metadata->setPrimaryTable([
            'name'    => 'labels_organizations',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne([
            'fieldName'    => 'organization',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
            'id'           => true,
            'mappedBy'     => null,
            'inversedBy'   => 'labels',
            'joinColumns'  => [
                [
                    'name'                 => 'organization_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapField([
            'fieldName'  => 'label',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'label',
            'id'         => true,
        ]);
    }
}
