<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\NewsComment as NewsCommentRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Comments on articles.
 */
class NewsComment extends CommentAbstract
{
    const OBJ_PROP = 'news';

    /**
     * The news this comment belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\News>")
     * @JMS\Groups({"list", "details"})
     *
     * @var News
     */
    protected $news;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = NewsCommentRepository::class;
        $metadata->setPrimaryTable([
            'name'    => 'news_comments',
            'indexes' => [
                'status_idx' => ['columns' => ['status', 'is_reviewed']],
            ],
        ]);
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
            'fieldName'  => 'ip_address',
            'type'       => 'string',
            'length'     => 30,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ip_address',
        ]);
        $metadata->mapField([
            'fieldName'  => 'visitor_id',
            'type'       => 'string',
            'length'     => 120,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'visitor_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'email',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'email',
        ]);
        $metadata->mapField([
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'website',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'website',
        ]);
        $metadata->mapField([
            'fieldName'  => 'content',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content',
        ]);
        $metadata->mapField([
            'fieldName'  => 'status',
            'type'       => 'string',
            'length'     => 30,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_reviewed',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_reviewed',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'news',
            'targetEntity' => News::class,
            'mappedBy'     => null,
            'inversedBy'   => 'comments',
            'joinColumns'  => [
                [
                    'name'                 => 'news_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}
