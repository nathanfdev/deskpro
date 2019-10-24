<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class CommunityTopicStatusTransition
 *
 * @package Application\DeskPRO\Entity
 */
class CommunityTopicStatusTransition extends DomainObject
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var CommunityTopic
     */
    private $topic;

    /**
     * @var CommunityTopicStatusCategory|null
     */
    private $old_status_category;

    /**
     * @var CommunityTopicStatusCategory
     */
    private $new_status_category;

    /**
     * @var \DateTime
     */
    private $date_created;

    /**
     * CommunityTopicStatusTransition constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CommunityTopic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param CommunityTopic $topic
     * @return CommunityTopicStatusTransition
     */
    public function setTopic(CommunityTopic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return CommunityTopicStatusCategory|null
     */
    public function getOldStatusCategory()
    {
        return $this->old_status_category;
    }

    /**
     * @param CommunityTopicStatusCategory|null $old_status_category
     * @return CommunityTopicStatusTransition
     */
    public function setOldStatusCategory(CommunityTopicStatusCategory $old_status_category = null)
    {
        $this->old_status_category = $old_status_category;

        return $this;
    }

    /**
     * @return CommunityTopicStatusCategory
     */
    public function getNewStatusCategory()
    {
        return $this->new_status_category;
    }

    /**
     * @param CommunityTopicStatusCategory $new_status_category
     * @return CommunityTopicStatusTransition
     */
    public function setNewStatusCategory(CommunityTopicStatusCategory $new_status_category)
    {
        $this->new_status_category = $new_status_category;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * For criteria compat.
     *
     * @return \DateTime
     */
    public function getdate_created()
    {
        return $this->date_created;
    }

    /**
     * @param ClassMetadata $metadata
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name' => 'community_topic_status_transitions',
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
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
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'topic',
                'targetEntity' => CommunityTopic::class,
                'mappedBy'     => null,
                'inversedBy'   => 'status_transitions',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'topic_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'CASCADE',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'old_status_category',
                'targetEntity' => CommunityTopicStatusCategory::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'old_status_category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'CASCADE',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'new_status_category',
                'targetEntity' => CommunityTopicStatusCategory::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'new_status_category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'CASCADE',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}
