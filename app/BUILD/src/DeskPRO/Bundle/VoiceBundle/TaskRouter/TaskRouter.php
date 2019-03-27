<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\WorkflowInterface;
use DpSys\LowError\SystemErrorHandler;
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

                    try {
                        $this->dispatcher->dispatch(TaskRouterEvent::ERROR, new TaskRouterEvent($task));
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }

                    $this->storage->saveTask($task);
                }

                /** @var WorkflowInterface $workflow */
                $workflow = $this->container->get($this->workflows[$task->getChannel()]);

                // check if task is expired
                if ($workflow->isTaskTimedOut($task)) {
                    $task->setStatus(Task::STATUS_TIMEOUT);

                    // task is timed out, reset workers
                    $workers = $this->storage->getWorkers($task->getWorkerIds());
                    foreach ($workers as $worker) {
                        $worker->removePendingTask($task);
                        $this->storage->saveWorker($worker);
                    }

                    try {
                        $this->dispatcher->dispatch(TaskRouterEvent::TIMEOUT, new TaskRouterEvent($task));
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }

                    $this->storage->saveTask($task);
                } elseif ($task->isAssignExpired() || !$task->getWorkerIds()) {
                    // re-route timeout
                    if ($task->isAssignExpired()) {
                        $task->setDateExpireAssigned(null);
                        // remove pending task
                        $workers = $this->storage->getWorkers($task->getWorkerIds());
                        foreach ($workers as $worker) {
                            $task->removeWorker($worker);

                            $worker->removePendingTask($task);
                            $this->storage->saveWorker($worker);
                        }

                        try {
                            $this->dispatcher->dispatch(TaskRouterEvent::ASSIGN_TIMEOUT, new TaskRouterEvent($task));
                        } catch (\Exception $e) {
                            SystemErrorHandler::logException($e);
                        }

                        $this->storage->saveTask($task);
                    }

                    // if task wasn't assigned yet then try to find a worker for it
                    $workflow->assignTask($task, $workflow->getAvailableWorkers($task));

                    // we've found workers for the task
                    // update task
                    if ($task->getWorkerIds()) {
                        // reserve workers for the task
                        $workers = $this->storage->getWorkers($task->getWorkerIds());
                        foreach ($workers as $worker) {
                            $worker->addPendingTask($task);
                            $this->storage->saveWorker($worker);
                        }

                        try {
                            $this->dispatcher->dispatch(TaskRouterEvent::ASSIGNED, new TaskRouterEvent($task));
                        } catch (\Exception $e) {
                            SystemErrorHandler::logException($e);
                        }

                        $this->storage->saveTask($task);
                    }
                }
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
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
        if (!$taskId) {
            return false;
        }

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

            $acceptedWorker->addActiveTask($task);
            $this->storage->saveWorker($acceptedWorker);

            // task is accepted
            // remove pending task
            $workers = $this->storage->getWorkers($task->getWorkerIds());
            foreach ($workers as $worker) {
                if ($acceptedWorker->getId() !== $worker->getId()) {
                    $task->removeWorker($worker);
                }

                $worker->removePendingTask($task);
                $this->storage->saveWorker($worker);
            }

            // mark task as completed
            // e.g. phone call is already established and conference is started
            $task->setStatus(Task::STATUS_ACCEPTED);
            $task->setAcceptedWorkerId($acceptedWorker->getId());

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::ACCEPTED, new TaskRouterEvent($task));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
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
        if (!$taskId) {
            return false;
        }

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

            $worker->removePendingTask($task);
            $this->storage->saveWorker($worker);

            // add worker id to 'rejected' list
            $task->addRejectedBy($worker);
            $task->removeWorker($worker);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::REJECTED, new TaskRouterEvent($task));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int $taskId
     *
     * @return bool
     */
    public function endTask($taskId)
    {
        if (!$taskId) {
            return false;
        }

        $this->lock->acquire(true);

        try {
            $task = $this->storage->getTask($taskId);

            // reject all workers
            $workers = $this->storage->getWorkers($task->getWorkerIds());
            foreach ($workers as $worker) {
                $worker->removeActiveTask($task);
                $worker->removePendingTask($task);

                $this->storage->saveWorker($worker);
            }

            // if task is done then we can't reject it
            if ($task->isPending()) {
                $task->setStatus(Task::STATUS_CANCELED);
                $task->setWorkersIds([]);

                try {
                    $this->dispatcher->dispatch(TaskRouterEvent::CANCELED, new TaskRouterEvent($task));
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                }

                $this->storage->saveTask($task);
            }

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerId
     * @param bool   $ignoreRejected
     *
     * @return bool
     */
    public function canWorkerAcceptTask($taskId, $workerType, $workerId, $ignoreRejected = true)
    {
        $this->lock->acquire(true);

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                return false;
            }

            /** @var WorkflowInterface $workflow */
            $workflow = $this->container->get($this->workflows[$task->getChannel()]);
            if (!$workflow) {
                return false;
            }

            $availableWorkers = $workflow->getAvailableWorkers($task, $ignoreRejected);
            foreach ($availableWorkers as $availableWorker) {
                if ($availableWorker->getId() === $worker->getId()) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerId
     *
     * @return bool
     */
    public function completeTaskForWorker($taskId, $workerType, $workerId)
    {
        if (!$taskId) {
            return false;
        }

        $this->lock->acquire(true);

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                return false;
            }

            $worker->removeActiveTask($task);
            $worker->removePendingTask($task);

            $task->removeWorker($worker);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int $taskId
     *
     * @return bool
     */
    public function resetWorkersForTask($taskId)
    {
        $this->lock->acquire(true);

        try {
            $task = $this->storage->getTask($taskId);
            if (!$task) {
                return false;
            }

            foreach ($task->getWorkerIds() as $workerId) {
                $worker = $this->storage->getWorker($workerId);
                if ($worker) {
                    $worker->removeActiveTask($task);
                    $worker->removePendingTask($task);

                    $this->storage->saveWorker($worker);
                }
            }

            $task->setWorkersIds([]);
            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerId
     *
     * @return bool
     */
    public function reserveAnotherWorkerForTask($taskId, $workerType, $workerId)
    {
        $this->lock->acquire(true);

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                return false;
            }

            $task->addWorker($worker);
            $worker->addPendingTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerId
     *
     * @return bool
     */
    public function rejectAnotherWorkerReservation($taskId, $workerType, $workerId)
    {
        $this->lock->acquire(true);

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                return false;
            }

            $task->removeWorker($worker);

            $worker->removeActiveTask($task);
            $worker->removePendingTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param int    $taskId
     * @param string $workerType
     * @param int    $workerId
     *
     * @return bool
     */
    public function joinTask($taskId, $workerType, $workerId)
    {
        $this->lock->acquire(true);

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                return false;
            }

            $task->addWorker($worker);

            $worker->removePendingTask($task);
            $worker->addActiveTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }
}
