<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Helper\WorkerHelper;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;

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
    private $settingsResolver;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * Constructor.
     *
     * @param WorkerHelper            $workerHelper
     * @param VoiceTaskHelper         $taskHelper
     * @param VoiceSettingsResolver   $settingsResolver
     * @param StorageAdapterInterface $storage
     */
    public function __construct(
        WorkerHelper            $workerHelper,
        VoiceTaskHelper         $taskHelper,
        VoiceSettingsResolver   $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $this->workerHelper     = $workerHelper;
        $this->taskHelper       = $taskHelper;
        $this->settingsResolver = $settingsResolver;
        $this->storage          = $storage;
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
    public function isTaskTimedOut(Task $task)
    {
        $now   = new \DateTime();
        $queue = $this->taskHelper->getVoiceQueue($task);
        if ($queue) {
            $timeout = $queue->getVoicemailTimeout();
        } else {
            $timeout = $this->settingsResolver->getVoiceSettings()->getAgentVoicemailTimeout();
        }

        $offset = $now->getTimestamp() - $task->getDateCreated()->getTimestamp();

        return $offset > $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableWorkers(Task $task)
    {
        /** @var Worker[] $availableAgentWorkers */
        $availableAgentWorkers = [];
        foreach ($this->storage->getOnlineWorkersByType('agent') as $worker) {
            $availableAgentWorkers[$worker->getId()] = $worker;
        }
        foreach ($this->workerHelper->getForwardingCallWorkers() as $worker) {
            $availableAgentWorkers[$worker->getId()] = $worker;
        }

        $voiceAgentIds = $this->workerHelper->getVoiceAgentIds();

        $availableAgentWorkers = array_filter(
            $availableAgentWorkers,
            function (Worker $worker) use ($task, $voiceAgentIds) {
                // ignore if agent has already rejected task
                if ($task->getRejectedBy() && in_array($worker->getId(), $task->getRejectedBy())) {
                    return false;
                }

                // ignore if agent is already on a call or has incoming call popup
                if ($worker->hasPendingTasksForChannel(self::getChannelName())
                    || $worker->hasActiveTasksForChannel(self::getChannelName())
                    || $worker->hasPendingTasksForChannel(ChatWorkflow::getChannelName())
                    || $worker->hasActiveTasksForChannel(ChatWorkflow::getChannelName())
                    || !in_array($worker->getTypeId(), $voiceAgentIds)
                ) {
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
            $queueAgentIds = $voiceQueue->getActiveAgentsPeopleIds();
            $taskQueue     = $this->storage->getTaskQueue(self::getChannelName(), $voiceQueue->getId());
            if (!$taskQueue) {
                $taskQueue = new TaskQueue();
                $taskQueue->setType(self::getChannelName());
                $taskQueue->setTypeId($voiceQueue->getId());
            }

            switch ($voiceQueue->getRoutingModel()) {
                case VoiceQueue::ROUTING_MODEL_AUTOMATIC:
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
                    // get answered call stat, order by answered calls count
                    // and get ids with max available count of workers option from queue settings
                    if (!is_array($taskQueue->getAttribute('answered_calls_counts'))) {
                        $taskQueue->setAttribute('answered_calls_counts', []);
                    }

                    $answeredCallsCounts = $taskQueue->getAttribute('answered_calls_counts');
                    if (is_array($answeredCallsCounts)) {
                        // check if 'answered_calls_counts' is up to date with voice queue agents list
                        foreach ($answeredCallsCounts as $agentId => $callsCount) {
                            if (!in_array($agentId, $queueAgentIds)) {
                                unset($answeredCallsCounts[$agentId]);
                            }
                        }
                        foreach ($queueAgentIds as $agentId) {
                            if (!isset($answeredCallsCounts[$agentId])) {
                                $answeredCallsCounts[$agentId] = 0;
                            }
                        }

                        // sort by least utilized
                        // and return limited by max allowed workers number
                        asort($answeredCallsCounts);

                        $workerIds = [];
                        foreach ($answeredCallsCounts as $agentId => $callsCount) {
                            if (isset($workerToAgentMap[$agentId])) {
                                $workerIds[] = $workerToAgentMap[$agentId];
                            }

                            if (count($workerIds) >= $voiceQueue->getMaxQueueSize()) {
                                break;
                            }
                        }

                        $task->setWorkersIds($workerIds);
                    }

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
