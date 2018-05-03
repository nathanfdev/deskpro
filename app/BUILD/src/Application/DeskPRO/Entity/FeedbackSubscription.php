<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class FeedbackSubscription extends \Application\DeskPRO\Domain\DomainObject
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
     * @var \Application\DeskPRO\Entity\Feedback
     */
    protected $feedback;

    /**
     * @var bool
     */
    protected $root_category;

    /**
     * @param Feedback $feedback
     */
    public function setFeedback(Feedback $feedback = null)
    {
        $this->setModelField('feedback', $feedback);
    }

    /**
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->root_category;
    }

    /**
     * @param bool $root_category
     */
    public function setRootCategory($root_category)
    {
        $this->setModelField('root_category', $root_category);
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
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\FeedbackSubscription';
        $metadata->setPrimaryTable(
            [
                'name'    => 'feedback_subscriptions',
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
                'fieldName'    => 'feedback',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Feedback',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'feedback_id',
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
    }
}
