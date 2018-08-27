<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\WorkflowInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockInterface;

/**
 * Class TaskRouter.
 */
class TaskRouter
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LockInterface
     */
    private $lock;

    /**
     * @var WorkflowInterface[]
     */
    private $workflows;

    /**
     * Constructor.
     *
     * @param ContainerInterface       $container
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     * @param LockInterface            $lock
     */
    public function __construct(
        ContainerInterface       $container,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher,
        LockInterface            $lock
    ) {
        $this->container  = $container;
        $this->storage    = $storage;
        $this->dispatcher = $dispatcher;
        $this->lock       = $lock;
    }

    /**
     * @param WorkflowInterface[] $workflows
     */
    public function setWorkflows($workflows)
    {
        $this->workflows = $workflows;
    }

    public function evaluate()
    {
        $this->lock->acquire(true);

        try {
            $tasks = $this->storage->getActiveTasks();

            // sort tasks by priority
            usort($tasks, function (Task $task1, Task $task2) {
                return $task1->getPriority() - $task2->getPriority();
            });

            foreach ($tasks as $task) {
                // get task workflow
                if (!isset($this->workflows[$task->getChannel()])) {
                    $task->setStatus(Task::STATUS_ERROR);
                    $task->setStatusReason('Unknown workflow');

                    $this->dispatcher->dispatch(TaskRouterEvent::ERROR, new TaskRouterEvent($task));
                    $this->storage->saveTask($task);
                }

                $workflow = $this->container->get($this->workflows[$task->getChannel()]);

                // check if task is expired
                if ($workflow->isTaskTimedOut($task)) {
                    $task->setStatus(Task::STATUS_TIMEOUT);

                    $this->dispatcher->dispatch(TaskRouterEvent::TIMEOUT, new TaskRouterEvent($task));
                    $this->storage->saveTask($task);
                } elseif (!$task->getWorkerIds()) {
                    // if task wasn't assigned yet then try to find a worker for it
                    $workflow->assignTask($task);

                    // we've found workers for the task
                    // update task
                    if ($task->getWorkerIds()) {
                        // reserve workers for the task
                        $workers = $this->storage->getWorkers($task->getWorkerIds());
                        foreach ($workers as $worker) {
                            if ($worker->isAvailable()) {
                                $worker->setActivity(Worker::ACTIVITY_RESERVED);

                                $this->storage->saveWorker($worker);
                            }
                        }

                        $this->dispatcher->dispatch(TaskRouterEvent::ASSIGNED, new TaskRouterEvent($task));
                        $this->storage->saveTask($task);
                    }
                }
            }
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerTypeId
     *
     * @return bool
     */
    public function acceptTask($taskId, $workerType, $workerTypeId)
    {
        $this->lock->acquire(true);

        try {
            $task = $this->storage->getTask($taskId);

            // if task is done then we can't accept it
            if (!$task->isPending()) {
                return false;
            }

            // get worker for the task and mark it as 'busy'
            $acceptedWorker = $this->storage->getWorkerByType($workerType, $workerTypeId);
            if (!$acceptedWorker || !in_array($acceptedWorker->getId(), $task->getWorkerIds())) {
                return false;
            }

            $acceptedWorker->setActivity(Worker::ACTIVITY_BUSY);
            $this->storage->saveWorker($acceptedWorker);

            // task is accepted
            // other workers should become 'idle'
            $workers = $this->storage->getWorkers($task->getWorkerIds());
            foreach ($workers as $worker) {
                if ($worker->getId() !== $acceptedWorker->getId()) {
                    $task->removeWorkerId($worker->getId());

                    $worker->setActivity(Worker::ACTIVITY_IDLE);
                    $this->storage->saveWorker($worker);
                }
            }

            // mark task as completed
            // e.g. phone call is already established and conference is started
            $task->setStatus(Task::STATUS_DONE);
            $task->setAcceptedWorkerId($acceptedWorker->getId());

            $this->dispatcher->dispatch(TaskRouterEvent::ACCEPTED, new TaskRouterEvent($task));
            $this->storage->saveTask($task);

            return true;
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerTypeId
     *
     * @return bool
     */
    public function rejectTask($taskId, $workerType, $workerTypeId)
    {
        $this->lock->acquire(true);

        try {
            $task = $this->storage->getTask($taskId);

            // if task is done then we can't reject it
            if (!$task->isPending()) {
                return false;
            }

            // get worker for the task and mark it as 'idle'
            $worker = $this->storage->getWorkerByType($workerType, $workerTypeId);
            if (!$worker || !in_array($worker->getId(), $task->getWorkerIds())) {
                return false;
            }

            $worker->setActivity(Worker::ACTIVITY_IDLE);
            $this->storage->saveWorker($worker);

            // add worker id to 'rejected' list
            $task->addRejectedBy($worker->getId());
            $task->removeWorkerId($worker->getId());

            $this->dispatcher->dispatch(TaskRouterEvent::REJECTED, new TaskRouterEvent($task));
            $this->storage->saveTask($task);

            return true;
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int $taskId
     *
     * @return bool
     */
    public function cancelTask($taskId)
    {
        $this->lock->acquire(true);

        try {
            $task = $this->storage->getTask($taskId);

            // if task is done then we can't reject it
            if (!$task->isPending()) {
                return false;
            }

            // task is done
            // reject all workers
            $workers = $this->storage->getWorkers($task->getWorkerIds());
            foreach ($workers as $worker) {
                $worker->setActivity(Worker::ACTIVITY_IDLE);
                $this->storage->saveWorker($worker);
            }

            $task->setStatus(Task::STATUS_CANCELED);
            $task->setWorkersIds([]);

            $this->dispatcher->dispatch(TaskRouterEvent::CANCELED, new TaskRouterEvent($task));
            $this->storage->saveTask($task);

            return true;
        } finally {
            $this->lock->release();
        }
    }
}
