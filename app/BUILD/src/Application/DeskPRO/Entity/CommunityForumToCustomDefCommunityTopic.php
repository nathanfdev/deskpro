<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CommunityForumToCustomDefCommunityTopic.
 *
 * @JMS\ExclusionPolicy("all")
 */
class CommunityForumToCustomDefCommunityTopic extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var CommunityForum
     */
    protected $forum;

    /**
     * @var CustomDefCommunityTopic
     */
    protected $field;

    /**
     * @var int
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $display_order = 0;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CommunityForum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @param CommunityForum $forum
     *
     * @return CommunityForumToCustomDefCommunityTopic
     */
    public function setForum(CommunityForum $forum = null)
    {
        $this->setModelField('forum', $forum);

        return $this;
    }

    /**
     * @return CustomDefCommunityTopic
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param CustomDefCommunityTopic $field
     *
     * @return CommunityForumToCustomDefCommunityTopic
     */
    public function setField(CustomDefCommunityTopic $field = null)
    {
        $this->setModelField('field', $field);

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
     *
     * @return CommunityForumToCustomDefCommunityTopic
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', (int) $display_order);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\CommunityForumToStatus::class;
        $metadata->setPrimaryTable([
            'name'              => 'community_forum_to_custom_def_community_topic',
            'uniqueConstraints' => [
                'unique_key_idx' => [
                    'columns' => [
                        'forum_id',
                        'field_id',
                    ],
                ],
            ],
        ]);
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
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );

        $metadata->mapManyToOne([
            'fieldName'    => 'forum',
            'targetEntity' => CommunityForum::class,
            'inversedBy'   => 'topic_fields',
            'joinColumns'  => [
                [
                    'name'                 => 'forum_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'CASCADE',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'field',
            'targetEntity' => CustomDefCommunityTopic::class,
            'inversedBy'   => 'forums',
            'joinColumns'  => [
                [
                    'name'                 => 'field_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'CASCADE',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
