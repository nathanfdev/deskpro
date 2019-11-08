<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Helper\WorkerHelper;
use DeskPRO\Bundle\VoiceBundle\Permissions\VoicePermissionsChecker;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Psr\Log\LoggerInterface;

/**
 * Class VoiceWorkflow.
 */
class VoiceWorkflow implements WorkflowInterface
{
    /**
     * @var WorkerHelper
     */
    private $workerHelper;

    /**
     * @var VoiceTaskHelper
     */
    private $taskHelper;

    /**
     * @var VoiceSettingsResolver
     */
    private $voiceSettingsResolver;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var VoicePermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param WorkerHelper            $workerHelper
     * @param VoiceTaskHelper         $taskHelper
     * @param VoiceSettingsResolver   $voiceSettingsResolver
     * @param StorageAdapterInterface $storage
     * @param VoicePermissionsChecker $permissionsChecker
     * @param LoggerInterface         $logger
     */
    public function __construct(
        WorkerHelper            $workerHelper,
        VoiceTaskHelper         $taskHelper,
        VoiceSettingsResolver   $voiceSettingsResolver,
        StorageAdapterInterface $storage,
        VoicePermissionsChecker $permissionsChecker,
        LoggerInterface         $logger
    ) {
        $this->workerHelper          = $workerHelper;
        $this->taskHelper            = $taskHelper;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->storage               = $storage;
        $this->permissionsChecker    = $permissionsChecker;
        $this->logger                = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getChannelName()
    {
        return 'voice';
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(Task $task)
    {
        $queue = $this->taskHelper->getVoiceQueue($task);
        if ($queue) {
            return $queue->getVoicemailTimeout();
        } else {
            return $this->voiceSettingsResolver->getVoiceSettings()->getAgentVoicemailTimeout();
        }
    }

    /**
     * @param Worker $worker
     * @param bool   $withLog
     *
     * @return bool
     */
    public function workerIsBusy(Worker $worker, $withLog = false)
    {
        if ($withLog) {
            $this->logger->info(sprintf(
                '[VoiceWorkflow] Check if worker is busy, worker_id = %s',
                $worker->getTypeId()
            ));
        }

        if ($worker->hasPendingTasksForChannel(self::getChannelName())) {
            if ($withLog) {
                $this->logger->info(sprintf(
                    '[VoiceWorkflow] Worker is busy, reason = has_pending_phone_call, worker_id = %s',
                    $worker->getTypeId()
                ));
            }

            return true;
        }

        if ($worker->hasActiveTasksForChannel(self::getChannelName())) {
            if ($withLog) {
                $this->logger->info(sprintf(
                    '[VoiceWorkflow] Worker is busy, reason = has_active_phone_call, worker_id = %s',
                    $worker->getTypeId()
                ));
            }

            return true;
        }

        $voiceAgentIds = $this->workerHelper->getVoiceAgentIds();
        if (!in_array($worker->getTypeId(), $voiceAgentIds)) {
            if ($withLog) {
                $this->logger->info(sprintf(
                    '[VoiceWorkflow] Worker is not available for chat, worker_id = %s',
                    $worker->getTypeId()
                ));
            }

            return true;
        }

        if ($withLog) {
            $this->logger->info(sprintf(
                '[VoiceWorkflow] Worker is not busy, worker_id = %s',
                $worker->getTypeId()
            ));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableWorkers(Task $task, $ignoreRejected = false)
    {
        /** @var Worker[] $availableAgentWorkers */
        $availableAgentWorkers = [];
        foreach ($this->storage->getOnlineWorkersByType('agent') as $worker) {
            $availableAgentWorkers[$worker->getId()] = $worker;
        }
        foreach ($this->workerHelper->getForwardingCallWorkers() as $worker) {
            $availableAgentWorkers[$worker->getId()] = $worker;
        }

        $availableAgentWorkers = array_filter(
            $availableAgentWorkers,
            function (Worker $worker) use ($task, $ignoreRejected) {
                // ignore if agent has already rejected task
                if (!$ignoreRejected && $task->getRejectedBy() && in_array($worker->getId(), $task->getRejectedBy())) {
                    $this->logger->info(sprintf(
                        '[VoiceWorkflow] Worker rejected the task, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                // ignore if agent is already on a call or has incoming call popup
                // or has active chats
                if ($this->workerIsBusy($worker, true)) {
                    $this->logger->info(sprintf(
                        '[VoiceWorkflow] Worker is busy, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                return true;
            }
        );

        return $availableAgentWorkers;
    }

    /**
     * {@inheritdoc}
     */
    public function assignTask(Task $task, array $workers)
    {
        // get available workers
        $workerToAgentMap = [];
        foreach ($workers as $worker) {
            $workerToAgentMap[$worker->getTypeId()] = $worker->getId();
        }

        $availableWorkerAgentIds = array_map(function (Worker $worker) {
            return $worker->getTypeId();
        }, $workers);

        // get workers for the task
        $voiceQueue = $this->taskHelper->getVoiceQueue($task);
        $agent      = $this->taskHelper->getWorkerAgent($task);

        if ($voiceQueue) {
            $this->logger->info(sprintf(
                '[VoiceWorkflow] Voice queue, queue_id = %s, task_id = %s',
                $voiceQueue->getId(), $task->getId()
            ));

            $queueAgentIds = [];
            foreach ($voiceQueue->getActiveAgentsPeople() as $agent) {
                if ($this->permissionsChecker->canBeMemberOfVoiceQueue($voiceQueue, $agent)) {
                    $queueAgentIds[] = $agent->getId();

                    $this->logger->info(sprintf(
                        '[VoiceWorkflow] Agent can be a member of this voice queue, queue_id = %s, agent_id = %s task_id = %s',
                        $voiceQueue->getId(), $agent->getId(), $task->getId()
                    ));
                } else {
                    $this->logger->info(sprintf(
                        '[VoiceWorkflow] Agent can not be a member of this voice queue, queue_id = %s, agent_id = %s task_id = %s',
                        $voiceQueue->getId(), $agent->getId(), $task->getId()
                    ));
                }
            }

            $taskQueue = $this->storage->getTaskQueue(self::getChannelName(), $voiceQueue->getId());
            if (!$taskQueue) {
                $taskQueue = new TaskQueue();
                $taskQueue->setType(self::getChannelName());
                $taskQueue->setTypeId($voiceQueue->getId());
            }

            switch ($voiceQueue->getRoutingModel()) {
                case VoiceQueue::ROUTING_MODEL_ROUND_ROBIN:
                    // round robin order list is not yet, set default one
                    if (!is_array($taskQueue->getAttribute('round_robin_order'))) {
                        $taskQueue->setAttribute('round_robin_order', $queueAgentIds);
                    }

                    // iterate over task queue's round robin list to get first available worker
                    // the first found one will be used as task consumer
                    // and pushed to the end of the list so we will get the next one for the next task
                    $roundRobinList = $taskQueue->getAttribute('round_robin_order');
                    if (is_array($roundRobinList)) {
                        // check if 'round_robin_order' is up to date with voice queue agents list
                        foreach ($roundRobinList as $num => $agentId) {
                            if (!in_array($agentId, $queueAgentIds)) {
                                unset($roundRobinList[$num]);
                            }
                        }

                        $roundRobinList = array_values($roundRobinList);
                        foreach ($queueAgentIds as $agentId) {
                            if (!in_array($agentId, $roundRobinList)) {
                                $roundRobinList[] = $agentId;
                            }
                        }

                        // get next worker for the task
                        $roundRobinList = array_values($roundRobinList);
                        foreach ($roundRobinList as $num => $agentId) {
                            if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                                $task->setWorkersIds([$workerToAgentMap[$agentId]]);
                                $task->setDateExpireAssignedOffset($voiceQueue->getAnswerTimeout());

                                // worker was fetched push to the end of the list
                                unset($roundRobinList[$num]);
                                $roundRobinList[] = $agentId;
                                $roundRobinList   = array_values($roundRobinList);

                                break;
                            }
                        }

                        $taskQueue->setAttribute('round_robin_order', $roundRobinList);
                    }

                    break;
                case VoiceQueue::ROUTING_MODEL_LEAST_UTILIZED:
                    $leastUtilizedWorkers = $workers;
                    $leastUtilizedWorkers = array_filter($leastUtilizedWorkers, function (Worker $worker) use ($queueAgentIds) {
                        return in_array($worker->getTypeId(), $queueAgentIds) && $worker->getType() === 'agent';
                    });
                    usort($leastUtilizedWorkers, function (Worker $a, Worker $b) {
                        return $a->getLastCallAt() > $b->getLastCallAt();
                    });

                    $leastUtilizedWorkers = array_splice($leastUtilizedWorkers, 0, $voiceQueue->getMaxQueueSize());
                    $workerIds            = array_map(function (Worker $worker) {
                        return $worker->getId();
                    }, $leastUtilizedWorkers);

                    $task->setWorkersIds($workerIds);
                    $task->setDateExpireAssignedOffset($voiceQueue->getAnswerTimeout());

                    break;
                case VoiceQueue::ROUTING_MODEL_SIMULRING:
                    $agentIds  = array_values(array_intersect($queueAgentIds, $availableWorkerAgentIds));
                    $workerIds = [];

                    foreach ($agentIds as $agentId) {
                        if (isset($workerToAgentMap[$agentId])) {
                            $workerIds[] = $workerToAgentMap[$agentId];
                        }
                    }

                    $task->setWorkersIds($workerIds);

                    break;
            }

            $this->storage->saveTaskQueue($taskQueue);
        } elseif ($agent) {
            if (in_array($agent->getId(), $availableWorkerAgentIds)
                && isset($workerToAgentMap[$agent->getId()])
            ) {
                $task->setWorkersIds([$workerToAgentMap[$agent->getId()]]);
            }
        }
    }
}
