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
 * Labels on feedbacks.
 */
class LabelFeedback extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'feedback';

    /**
     * @var \Application\DeskPRO\Entity\CommunityTopic
     */
    protected $topic;

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic(CommunityTopic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelFeedback';
        $metadata->setPrimaryTable(
            [
                'name'    => 'labels_community_topics',
                'indexes' => [
                    'label_idx' => ['columns' => ['label']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'topic',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CommunityTopic',
                'id'           => true,
                'mappedBy'     => null,
                'inversedBy'   => 'labels',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'topic_id',
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
