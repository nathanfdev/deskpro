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
 * Records labels on task.
 */
class LabelTask extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'task';

    /**
     * @var \Application\DeskPRO\Entity\Task
     */
    protected $task;

    public function setTask($task = null)
    {
        $this->setModelField('task', $task);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelTask';
        $metadata->setPrimaryTable([
            'name'    => 'labels_tasks',
            'indexes' => [
                'label_idx' => ['columns' => ['label']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne([
            'fieldName'    => 'task',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Task',
            'id'           => true,
            'mappedBy'     => null,
            'inversedBy'   => null,
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
        $metadata->mapField([
            'fieldName'  => 'label',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'label',
            'id'         => true,
        ]);
    }
}
