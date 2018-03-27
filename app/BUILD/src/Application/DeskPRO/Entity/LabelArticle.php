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
class LabelArticle extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'articles';

    /**
     * @var \Application\DeskPRO\Entity\Article
     */
    protected $article;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelArticle';
        $metadata->setPrimaryTable([
            'name'    => 'labels_articles',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne([
            'fieldName'    => 'article',
            'id'           => true,
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Article',
            'mappedBy'     => null,
            'inversedBy'   => 'labels',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'article_id',
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
