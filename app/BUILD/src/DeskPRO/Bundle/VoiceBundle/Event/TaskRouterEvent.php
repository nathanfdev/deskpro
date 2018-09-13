<?php

namespace DeskPRO\Bundle\VoiceBundle\Event;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class VoiceEvent.
 */
class TaskRouterEvent extends Event
{
    const ASSIGNED = 'task_router.assigned';
    const TIMEOUT  = 'task_router.timeout';
    const ERROR    = 'task_router.error';
    const ACCEPTED = 'task_router.accepted';
    const REJECTED = 'task_router.rejected';
    const CANCELED = 'task_router.canceled';

    /**
     * @var Task
     */
    private $task;

    /**
     * Constructor.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }
}
