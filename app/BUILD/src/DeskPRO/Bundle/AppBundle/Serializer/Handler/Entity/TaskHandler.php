<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Task;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Task as TaskModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class TaskHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return Task::class;
    }

    /**
     * @param Task                         $entity
     * @param SideloadSerializationContext $context
     *
     * @return TaskModel
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new TaskModel($entity);

        return $model;
    }
}
