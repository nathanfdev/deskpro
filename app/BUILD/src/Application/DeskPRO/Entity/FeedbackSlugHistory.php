<?php

/**
 * DeskPRO.
 *
 * @category Entities
 *
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * History of slugs.
 */
class FeedbackSlugHistory extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The content.
     *
     * @var Feedback
     */
    protected $topic;

    /**
     * The slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @param Feedback $topic    the content
     * @param string   $old_slug the slug to put in history
     */
    public function __construct(Feedback $topic, $old_slug)
    {
        $this->setContent($topic);
        $this->setSlug($old_slug);
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->topic;
    }

    /**
     * @param Feedback $topic
     */
    public function setContent(Feedback $topic)
    {
        $this->setModelField('topic', $topic);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\FeedbackSlugHistory';
        $metadata->setPrimaryTable(['name' => 'community_topic_slug_history']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
            'fieldName'  => 'slug',
            'type'       => 'string',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'slug',
            'unique'     => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'topic',
            'targetEntity' => 'Application\DeskPRO\Entity\Feedback',
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'inversedBy'  => 'slug_history',
            'joinColumns' => [
                [
                    'name'                 => 'topic_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'cascade',
                    'nullable'             => false,
                ],
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
