<?php

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\TopicSubscription as TopicSubscriptionRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TopicSubscription extends \Application\DeskPRO\Domain\DomainObject
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
     * @var Topic
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
     * @param Topic $topic
     */
    public function setTopic(Topic $topic = null)
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
    
    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = TopicSubscriptionRepository::class;
        $metadata->setPrimaryTable(
            [
                'name'    => 'topic_subscriptions',
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
                'targetEntity' => Person::class,
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
                'targetEntity' => Topic::class,
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
