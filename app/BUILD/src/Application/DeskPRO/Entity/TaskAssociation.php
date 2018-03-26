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
 * Base class for Task Associations.
 */
abstract class TaskAssociation extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Task
     */
    protected $task;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     *
     * @return $this
     */
    public function setTask($task)
    {
        $this->setModelField('task', $task);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE);
        $metadata->setDiscriminatorColumn([
            'name'   => 'assoc_type',
            'type'   => 'string',
            'length' => '50',
        ]);
        $metadata->setDiscriminatorMap([
            'person'       => 'TaskAssociatedPerson',
            'ticket'       => 'TaskAssociatedTicket',
            'organization' => 'TaskAssociatedOrganization',
            //'deal' => 'TaskAssociatedDeal',
        ]);
        $metadata->setPrimaryTable(['name' => 'task_associations']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'task',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Task',
            'mappedBy'     => null,
            'inversedBy'   => 'task_associations',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'task_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
