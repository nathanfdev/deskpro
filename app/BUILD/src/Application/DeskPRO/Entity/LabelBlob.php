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
 * Labels on blobs.
 */
class LabelBlob extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'blobs';

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelBlob';
        $metadata->setPrimaryTable([
            'name'    => 'labels_blobs',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne([
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'id'           => true,
            'mappedBy'     => null,
            'inversedBy'   => 'labels',
            'joinColumns'  => [
                [
                    'name'                 => 'blob_id',
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
