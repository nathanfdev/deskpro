<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\WorkflowInterface;
use DpSys\LowError\SystemErrorHandler;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ContainerInterface       $container
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     * @param LockInterface            $lock
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ContainerInterface       $container,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher,
        LockInterface            $lock,
        LoggerInterface          $logger
    ) {
        $this->container  = $container;
        $this->storage    = $storage;
        $this->dispatcher = $dispatcher;
        $this->lock       = $lock;
        $this->logger     = $logger;
    }

    /**
     * @param WorkflowInterface[] $workflows
     */
    public function setWorkflows($workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateExpire(Task $task)
    {
        $date = clone $task->getDateCreated();

        /** @var WorkflowInterface $workflow */
        $workflow = $this->container->get($this->workflows[$task->getChannel()]);
        if ($workflow) {
            $timeout = $workflow->getTimeout($task);
            $date->modify("+{$timeout} seconds");
        }

        return $date;
    }

    public function evaluate()
    {
        try {
            $this->lock->acquire(true);
        } catch (\Exception $e) {
            $this->logger->info('[TaskRouter] Failed to aquire lock: '.$e->getMessage());

            return;
        }

        try {
            $tasks = $this->storage->getActiveTasks();

            // sort tasks by priority
            usort($tasks, function (Task $task1, Task $task2) {
                return $task1->getPriority() - $task2->getPriority();
            });

            foreach ($tasks as $task) {
                $this->logger->info(sprintf('[TaskRouter] Run task, task_id = %s', $task->getId()));

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
                    $this->logger->info(sprintf('[TaskRouter] No workflow was found for the task, mark as failed, task_id = %s', $task->getId()));
                }

                // if date expire is not set for the task
                // then set default date expire
                if (!$task->getDateExpire()) {
                    $task->setDateExpire($this->getDateExpire($task));
                    $this->storage->saveTask($task);
                }

                // check if task is expired
                if ($task->getDateExpire() <= new \DateTime()) {
                    $task->setStatus(Task::STATUS_TIMEOUT);

                    // task is timed out, reset workers
                    $workers = $this->storage->getWorkers($task->getWorkerIds());
                    foreach ($workers as $worker) {
                        $worker->removePendingTask($task);
                        $worker->removeActiveTask($task);

                        $this->storage->saveWorker($worker);
                    }

                    try {
                        $this->dispatcher->dispatch(TaskRouterEvent::TIMEOUT, new TaskRouterEvent($task));
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }

                    $this->storage->saveTask($task);
                    $this->logger->info(sprintf('[TaskRouter] Task is timed out, task_id = %s', $task->getId()));
                } elseif ($task->isAssignExpired() || !$task->getWorkerIds()) {
                    $assignTimeout = false;

                    // re-route timeout
                    if ($task->isAssignExpired()) {
                        $task->setDateExpireAssigned(null);

                        // trigger event before removing workers
                        // so we can get the list of the workers in event handlers
                        try {
                            $this->dispatcher->dispatch(TaskRouterEvent::ASSIGN_TIMEOUT, new TaskRouterEvent($task));
                        } catch (\Exception $e) {
                            SystemErrorHandler::logException($e);
                        }

                        // remove pending task
                        $workers = $this->storage->getWorkers($task->getWorkerIds());
                        foreach ($workers as $worker) {
                            $worker->removePendingTask($task);
                            $this->storage->saveWorker($worker);

                            // task was rejected by timeout
                            // don't assign this task to worker again
                            $task->addRejectedBy($worker);
                            $task->removeWorker($worker);

                            $this->logger->info(sprintf('[TaskRouter] Remove pending worker by assign timeout, task_id = %s', $task->getId()));
                        }

                        $this->storage->saveTask($task);
                        $assignTimeout = true;

                        $this->logger->info(sprintf('[TaskRouter] Assign timeout, task_id = %s', $task->getId()));
                    }

                    // if task wasn't assigned yet then try to find a worker for it
                    $this->logger->info(sprintf('[TaskRouter] Fetch available workers for the task, task_id = %s', $task->getId()));

                    /** @var WorkflowInterface $workflow */
                    $workflow = $this->container->get($this->workflows[$task->getChannel()]);
                    $workflow->assignTask($task, $workflow->getAvailableWorkers($task));

                    // we've found workers for the task
                    // update task
                    if ($task->getWorkerIds()) {
                        // modify date expire of the task to allow last worker to handle it
                        if ($task->getDateExpireAssigned() > $task->getDateExpire()) {
                            $task->setDateExpire(clone $task->getDateExpireAssigned());
                        }

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
                        $this->logger->info(sprintf(
                            '[TaskRouter] Found workers for the task, task_id = %s, worker_ids = [%s]',
                            $task->getId(), implode(', ', $task->getWorkerIds())
                        ));
                    } elseif ($assignTimeout || $task->getRejectedBy()) {
                        // no workers found after assign timeout
                        // or workers actively declined the call
                        // redirect directly to timeout handler
                        $task->setStatus(Task::STATUS_TIMEOUT);

                        try {
                            $this->dispatcher->dispatch(TaskRouterEvent::TIMEOUT, new TaskRouterEvent($task));
                        } catch (\Exception $e) {
                            SystemErrorHandler::logException($e);
                        }

                        // reset pending task
                        $workers = $this->storage->getWorkers($task->getWorkerIds());
                        foreach ($workers as $worker) {
                            $worker->removePendingTask($task);
                            $worker->removeActiveTask($task);

                            $this->storage->saveWorker($worker);

                            $task->removeWorker($worker);
                            $this->logger->info(sprintf('[TaskRouter] Remove pending worker by assign timeout, task_id = %s', $task->getId()));
                        }

                        $this->storage->saveTask($task);
                        $this->logger->info(sprintf('[TaskRouter] Task is timed out by assign timeout or has rejected workers, task_id = %s', $task->getId()));
                    }
                } else {
                    $this->logger->info(sprintf('[TaskRouter] No actions, task_id = %s', $task->getId()));
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
        $this->logger->info(sprintf(
            '[TaskRouter] Accept task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerTypeId
        ));

        try {
            $task = $this->storage->getTask($taskId);

            // if task is done then we can't accept it
            if (!$task->isPending()) {
                $this->logger->info(sprintf('[TaskRouter] Task is not pending, unable to accept, task_id = %s', $taskId));

                return false;
            }

            // get worker for the task and mark it as 'busy'
            $acceptedWorker = $this->storage->getWorkerByType($workerType, $workerTypeId);
            if (!$acceptedWorker || !in_array($acceptedWorker->getId(), $task->getWorkerIds())) {
                $this->logger->info(sprintf('[TaskRouter] Worker was not found or not in the list of pending workers, unable to accept, task_id = %s', $taskId));

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

                $this->logger->info(sprintf('[TaskRouter] Reset worker, task_id = %s, worker_id = %s', $taskId, $worker->getTypeId()));
            }

            // mark task as completed
            // e.g. phone call is already established and conference is started
            $task->setStatus(Task::STATUS_ACCEPTED);
            $task->setAcceptedWorkerId($acceptedWorker->getId());

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::ACCEPTED, new TaskRouterEvent($task, $acceptedWorker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->storage->saveTask($task);
            $this->logger->info(sprintf('[TaskRouter] Task is accepted, task_id = %s, worker_id = %s', $taskId, $workerTypeId));

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
        $this->logger->info(sprintf(
            '[TaskRouter] Reject task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerTypeId
        ));

        try {
            $task = $this->storage->getTask($taskId);

            // if task is done then we can't reject it
            if (!$task->isPending()) {
                $this->logger->info(sprintf('[TaskRouter] Task is not pending, unable to reject, task_id = %s', $taskId));

                return false;
            }

            // get worker for the task and mark it as 'idle'
            $worker = $this->storage->getWorkerByType($workerType, $workerTypeId);
            if (!$worker || !in_array($worker->getId(), $task->getWorkerIds())) {
                $this->logger->info(sprintf('[TaskRouter] Worker was not found or not in the list of pending workers, unable to reject, task_id = %s', $taskId));

                return false;
            }

            $worker->removePendingTask($task);
            $this->storage->saveWorker($worker);

            // add worker id to 'rejected' list
            $task->addRejectedBy($worker);
            $task->removeWorker($worker);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::REJECTED, new TaskRouterEvent($task, $worker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->storage->saveTask($task);
            $this->logger->info(sprintf('[TaskRouter] Task is rejected, task_id = %s, worker_id = %s', $taskId, $workerTypeId));

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
        $this->logger->info(sprintf('[TaskRouter] End task, task_id = %s', $taskId));

        try {
            $task = $this->storage->getTask($taskId);

            // reject all workers
            $workers = $this->storage->getWorkers($task->getWorkerIds());
            foreach ($workers as $worker) {
                $worker->removeActiveTask($task);
                $worker->removePendingTask($task);

                $this->storage->saveWorker($worker);

                try {
                    $this->dispatcher->dispatch(TaskRouterEvent::COMPLETE_WORKER, new TaskRouterEvent($task));
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                }

                $this->logger->info(sprintf('[TaskRouter] Reset worker, task_id = %s, worker_id = %s', $taskId, $worker->getTypeId()));
            }

            // if task is done then we can't reject it
            if ($task->isPending()) {
                $task->setStatus(Task::STATUS_CANCELED);
                $task->setWorkersIds([]);

                try {
                    $this->dispatcher->dispatch(TaskRouterEvent::TASK_CANCELED, new TaskRouterEvent($task));
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                }

                $this->storage->saveTask($task);
            } else {
                try {
                    $this->dispatcher->dispatch(TaskRouterEvent::TASK_COMPLETED, new TaskRouterEvent($task));
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                }
            }

            $this->logger->info(sprintf('[TaskRouter] The task is ended, task_id = %s', $taskId));

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
        $this->logger->info(sprintf(
            '[TaskRouter] Check if worker can accept the task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerId
        ));

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker or task found, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            /** @var WorkflowInterface $workflow */
            $workflow = $this->container->get($this->workflows[$task->getChannel()]);
            if (!$workflow) {
                $this->logger->info(sprintf(
                    '[TaskRouter] Unable to get workflow for the task, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            $availableWorkers = $workflow->getAvailableWorkers($task, $ignoreRejected);
            foreach ($availableWorkers as $availableWorker) {
                if ($availableWorker->getId() === $worker->getId()) {
                    $this->logger->info(sprintf(
                        '[TaskRouter] Worker is able to accept the task, task_id = %s, worker_type = %s, worker_id = %s',
                        $taskId, $workerType, $workerId
                    ));

                    return true;
                }
            }

            $this->logger->info(sprintf(
                '[TaskRouter] Worker does not have access to accept the task, task_id = %s, worker_type = %s, worker_id = %s',
                $taskId, $workerType, $workerId
            ));

            return false;
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
        $this->logger->info(sprintf(
            '[TaskRouter] Complete task for worker, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerId
        ));

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker or task found, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            $worker->removeActiveTask($task);
            $worker->removePendingTask($task);

            $task->removeWorker($worker);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::COMPLETE_WORKER, new TaskRouterEvent($task, $worker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->info(sprintf(
                '[TaskRouter] The task is completed for the worker, task_id = %s, worker_type = %s, worker_id = %s',
                $taskId, $workerType, $workerId
            ));

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
        $this->logger->info(sprintf("[TaskRouter] Reset task's workers, task_id = %s", $taskId));

        try {
            $task = $this->storage->getTask($taskId);
            if (!$task) {
                $this->logger->info(sprintf('[TaskRouter] No task found, skipping, task_id = %s', $taskId));

                return false;
            }

            foreach ($task->getWorkerIds() as $workerId) {
                $worker = $this->storage->getWorker($workerId);
                if ($worker) {
                    $worker->removeActiveTask($task);
                    $worker->removePendingTask($task);

                    $this->storage->saveWorker($worker);

                    try {
                        $this->dispatcher->dispatch(TaskRouterEvent::RESET_WORKER, new TaskRouterEvent($task, $worker));
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }

                    $this->logger->info(sprintf('[TaskRouter] Reset worker, task_id = %s, worker_id = %s', $taskId, $worker->getTypeId()));
                }
            }

            $task->setWorkersIds([]);
            $this->storage->saveTask($task);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::RESET_TASK, new TaskRouterEvent($task));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->info(sprintf("[TaskRouter] Task's workers are reset, task_id = %s", $taskId));

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
        $this->logger->info(sprintf(
            '[TaskRouter] Reserve another worker for the task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerId
        ));

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker or task found, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            $task->addWorker($worker);
            $worker->addPendingTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::ANOTHER_WORKER_RESERVED, new TaskRouterEvent($task, $worker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->info(sprintf(
                '[TaskRouter] Worker is reserved, task_id = %s, worker_type = %s, worker_id = %s',
                $taskId, $workerType, $workerId
            ));

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
        $this->logger->info(sprintf(
            '[TaskRouter] Reject worker reservation for the task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerId
        ));

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker or task found, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            $task->removeWorker($worker);

            $worker->removeActiveTask($task);
            $worker->removePendingTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::REJECTED_RESERVATION, new TaskRouterEvent($task, $worker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->info(sprintf(
                '[TaskRouter] Worker reservation is rejected, task_id = %s, worker_type = %s, worker_id = %s',
                $taskId, $workerType, $workerId
            ));

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
        $this->logger->info(sprintf(
            '[TaskRouter] Join task, task_id = %s, worker_type = %s, worker_id = %s',
            $taskId, $workerType, $workerId
        ));

        try {
            $task   = $this->storage->getTask($taskId);
            $worker = $this->storage->getWorkerByType($workerType, $workerId);

            if (!$task || !$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker or task found, skipping, task_id = %s, worker_type = %s, worker_id = %s',
                    $taskId, $workerType, $workerId
                ));

                return false;
            }

            if (!$task->isAccepted()) {
                $this->logger->info(sprintf(
                    '[TaskRouter] Task is not active, unable to join, task_id = %s, task_status = %s, worker_type = %s, worker_id = %s',
                    $taskId, $task->getStatus(), $workerType, $workerId
                ));

                return false;
            }

            $task->addWorker($worker);

            $worker->removePendingTask($task);
            $worker->addActiveTask($task);

            $this->storage->saveWorker($worker);
            $this->storage->saveTask($task);

            try {
                $this->dispatcher->dispatch(TaskRouterEvent::JOINED_TASK, new TaskRouterEvent($task, $worker));
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }

            $this->logger->info(sprintf(
                '[TaskRouter] Worker joined the task, task_id = %s, worker_type = %s, worker_id = %s',
                $taskId, $workerType, $workerId
            ));

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @param string $workerType
     * @param int    $workerId
     *
     * @return bool
     */
    public function updateLastWorkerActivity($workerType, $workerId)
    {
        $this->lock->acquire(true);
        $this->logger->info(sprintf(
            '[TaskRouter] Update last worker activity, worker_type = %s, worker_id = %s',
            $workerType, $workerId
        ));

        try {
            $worker = $this->storage->getWorkerByType($workerType, $workerId);
            if (!$worker) {
                $this->logger->info(sprintf(
                    '[TaskRouter] No worker found, skipping, worker_type = %s, worker_id = %s',
                    $workerType, $workerId
                ));

                return false;
            }

            $worker->setLastCallAt(new \DateTime());
            $this->storage->saveWorker($worker);

            $this->logger->info(sprintf(
                '[TaskRouter] Updated worker activity, worker_type = %s, worker_id = %s',
                $workerType, $workerId
            ));

            return true;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->lock->release();
        }
    }
}
