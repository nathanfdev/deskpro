<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\TopicSlugHistory as TopicSlugHistoryRepository;
use DateTime;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class TopicSlugHistory.
 */
class TopicSlugHistory extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var DateTime
     */
    protected $date_created;

    public function __construct(Topic $topic, $oldSlug)
    {
        $this->setContent($topic);
        $this->setSlug($oldSlug);
        $this->setDateCreated(new DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return TopicSlugHistory
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return Topic
     */
    public function getContent()
    {
        return $this->topic;
    }

    /**
     * @param Topic $topic
     *
     * @return TopicSlugHistory
     */
    public function setContent(Topic $topic)
    {
        $this->setModelField('topic', $topic);

        return $this;
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
     *
     * @return TopicSlugHistory
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     *
     * @return TopicSlugHistory
     */
    public function setDateCreated($date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = TopicSlugHistoryRepository::class;
        $metadata->setPrimaryTable(['name' => 'topic_slug_history']);
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
            'targetEntity' => Topic::class,
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
