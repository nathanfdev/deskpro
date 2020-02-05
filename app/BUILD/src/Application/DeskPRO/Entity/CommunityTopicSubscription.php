<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class CommunityTopicSubscription extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\CommunityTopic
     */
    protected $topic;

    /**
     * @var bool
     */
    protected $root_category;

    /**
     * Used with root_category when user subscribed to all brand root category.
     * Ideally we can remove root_category and just check brand but to support backward compatibility leave it
     *
     * @var \Application\DeskPRO\Entity\Brand
     */
    protected $rootCategoryBrand;

    /**
     * @param CommunityTopic $topic
     */
    public function setTopic(CommunityTopic $topic = null)
    {
        $this->setModelField('topic', $topic);
    }

    /**
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->root_category;
    }

    /**
     * @deprecated use setRootCategoryBrand instead
     *
     * @param bool $root_category
     */
    public function setRootCategory($root_category)
    {
        $this->setModelField('root_category', $root_category);
    }

    /**
     * @param \Application\DeskPRO\Entity\Brand $brand
     */
    public function setRootCategoryBrand(Brand $brand = null)
    {
        $this->setModelField('rootCategoryBrand', $brand);
        $this->setRootCategory($brand !== null);
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CommunityTopicSubscription';
        $metadata->setPrimaryTable(
            [
                'name'    => 'community_topic_subscriptions',
                'indexes' => [
                    'root_category_idx' => ['columns' => ['root_category']],
                ],
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
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'topic',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CommunityTopic',
                'mappedBy'     => null,
                'inversedBy'   => null,
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
                'fieldName'  => 'root_category',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'root_category',
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'rootCategoryBrand',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Brand',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'root_category_brand_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
