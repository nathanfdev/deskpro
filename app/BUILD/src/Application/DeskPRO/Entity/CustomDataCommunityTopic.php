<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\Basic;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Custom data.
 */
class CustomDataCommunityTopic extends CustomDataAbstract
{
    /**
     * @var CommunityTopic
     */
    protected $topic;

    /**
     * @var CustomDefCommunityTopic
     */
    protected $field;

    /**
     * @var CustomDefCommunityTopic
     */
    protected $root_field;

    public function getFeedbackId()
    {
        return $this->topic['id'];
    }

    /**
     * @return CommunityTopic
     */
    public function getFeedback()
    {
        return $this->topic;
    }

    /**
     * Set a field.
     *
     * @param CustomDefCommunityTopic $field
     *
     * @return $this
     */
    public function setField(CustomDefCommunityTopic $field = null)
    {
        $this->setModelField('field', $field);

        return $this;
    }

    /**
     * @return CustomDefAbstract
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set a root field.
     *
     * @param CustomDefCommunityTopic $field
     *
     * @return $this
     */
    public function setRootField(CustomDefCommunityTopic $field = null)
    {
        $this->setModelField('root_field', $field);

        return $this;
    }

    /**
     * @return CustomDefAbstract
     */
    public function getRootField()
    {
        return $this->root_field;
    }

    /**
     * {@inheritdoc}
     *
     * @return CommunityTopic
     */
    public function getOwner()
    {
        return $this->topic;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = Basic::class;
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name'              => 'custom_data_community_topic',
                'uniqueConstraints' => [],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
                'fieldName'  => 'value',
                'type'       => 'bigint',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'input',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'input',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'topic',
                'targetEntity' => CommunityTopic::class,
                'inversedBy'   => 'custom_data',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'topic_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'field',
                'targetEntity' => CustomDefCommunityTopic::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'root_field',
                'targetEntity' => CustomDefCommunityTopic::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_field_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
