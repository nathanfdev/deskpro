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
 * Labels on tickets.
 */
class LabelNews extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'news';

    /**
     * @var \Application\DeskPRO\Entity\News
     */
    protected $news;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelNews';
        $metadata->setPrimaryTable([
            'name'    => 'labels_news',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'news',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\News',
                'id'           => true,
                'mappedBy'     => null,
                'inversedBy'   => 'labels',
                'joinColumns'  => [
                    [
                        'name'                 => 'news_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'label',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'label',
                'id'         => true,
            ]
        );
    }
}
