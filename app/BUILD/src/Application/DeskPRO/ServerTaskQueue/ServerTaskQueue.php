<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ServerTaskQueue;

use Doctrine\ORM\EntityManager;

class ServerTaskQueue
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        $tasks            = $this->em->getRepository('DeskPRO:TaskQueue')->getPendingTasks(0);
        $show_task_status = count($tasks) > 0;

        return [
            'show_task_status' => $show_task_status,
            'tasks'            => $this->em->getRepository('DeskPRO:TaskQueue')->getAllTasks(),
        ];
    }
}
