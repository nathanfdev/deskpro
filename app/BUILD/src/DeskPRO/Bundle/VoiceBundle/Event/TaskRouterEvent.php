<?php

namespace DeskPRO\Bundle\VoiceBundle\Event;

use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class VoiceEvent.
 */
class TaskRouterEvent extends Event
{
    const ASSIGNED                = 'task_router.assigned';
    const ASSIGN_TIMEOUT          = 'task_router.assign_timeout';
    const TIMEOUT                 = 'task_router.timeout';
    const ERROR                   = 'task_router.error';
    const ACCEPTED                = 'task_router.accepted';
    const REJECTED                = 'task_router.rejected';
    const REJECTED_RESERVATION    = 'task_router.rejected_reservation';
    const TASK_CANCELED           = 'task_router.canceled';
    const ANOTHER_WORKER_RESERVED = 'task_router.reserved';
    const COMPLETE_WORKER         = 'task_router.completed_for_worker';
    const RESET_WORKER            = 'task_router.reset_worker';
    const RESET_TASK              = 'task_router.reset_task';
    const JOINED_TASK             = 'task_router.joined_task';

    /**
     * @var Task
     */
    private $task;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * Constructor.
     *
     * @param Task   $task
     * @param Worker $worker
     */
    public function __construct(Task $task, Worker $worker = null)
    {
        $this->task   = $task;
        $this->worker = $worker;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }
}
