<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CommunityForumToStatus
 *
 * @package Application\DeskPRO\Entity
 *
 * @JMS\ExclusionPolicy("all")
 */
class CommunityForumToStatus extends DomainObject
{
    /**
     * @var CommunityForum
     */
    protected $forum;

    /**
     * @var CommunityTopicStatusCategory
     */
    protected $status;

    /**
     * @var int
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $display_order = 0;

    /**
     * @return CommunityForum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @param CommunityForum $forum
     * @return CommunityForumToStatus
     */
    public function setForum(CommunityForum $forum)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return CommunityTopicStatusCategory
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param CommunityTopicStatusCategory $status
     * @return CommunityForumToStatus
     */
    public function setStatus(CommunityTopicStatusCategory $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     * @return CommunityForumToStatus
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = (int) $display_order;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\CommunityForumToStatus::class;
        $metadata->setPrimaryTable(['name' => 'community_forum_to_status']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );

        $metadata->mapManyToOne([
            'id' => true,
            'fieldName' => 'forum',
            'targetEntity' => CommunityForum::class,
            'inversedBy' => 'statuses',
            'joinColumns' => [
                [
                    'name' => 'forum_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                    'onDelete' => 'CASCADE',
                    'columnDefinition' => null,
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'id' => true,
            'fieldName' => 'status',
            'targetEntity' => CommunityTopicStatusCategory::class,
            'inversedBy' => 'topics',
            'joinColumns' => [
                [
                    'name' => 'status_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                    'onDelete' => 'CASCADE',
                    'columnDefinition' => null,
                ],
            ],
        ]);
    }
}
