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
 * Labels on downloads.
 */
class LabelDownload extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'downloads';

    /**
     * @var \Application\DeskPRO\Entity\Article
     */
    protected $download;

    /**
     * @return Article
     */
    public function getDownload()
    {
        return $this->download;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelDownload';
        $metadata->setPrimaryTable([
            'name'    => 'labels_downloads',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne([
            'fieldName'    => 'download',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Download',
            'id'           => true,
            'mappedBy'     => null,
            'inversedBy'   => 'labels',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'download_id',
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
