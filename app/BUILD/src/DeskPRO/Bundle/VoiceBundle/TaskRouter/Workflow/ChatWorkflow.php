<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Permissions\UserChatPermissionsChecker;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\UserChat\UserChatQueueTargetsLoader;
use Psr\Log\LoggerInterface;

/**
 * Class ChatWorkflow.
 */
class ChatWorkflow implements WorkflowInterface
{
    /**
     * @var ChatTaskHelper
     */
    private $taskHelper;

    /**
     * @var ChatSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var UserChatQueueTargetsLoader
     */
    private $targetsLoader;

    /**
     * @var UserChatPermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ChatTaskHelper             $taskHelper
     * @param ChatSettingsResolver       $settingsResolver
     * @param StorageAdapterInterface    $storage
     * @param UserChatQueueTargetsLoader $targetsLoader
     * @param UserChatPermissionsChecker $permissionsChecker
     * @param LoggerInterface            $logger
     */
    public function __construct(
        ChatTaskHelper             $taskHelper,
        ChatSettingsResolver       $settingsResolver,
        StorageAdapterInterface    $storage,
        UserChatQueueTargetsLoader $targetsLoader,
        UserChatPermissionsChecker $permissionsChecker,
        LoggerInterface            $logger
    ) {
        $this->taskHelper         = $taskHelper;
        $this->settingsResolver   = $settingsResolver;
        $this->storage            = $storage;
        $this->targetsLoader      = $targetsLoader;
        $this->permissionsChecker = $permissionsChecker;
        $this->logger             = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getChannelName()
    {
        return 'chat';
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(Task $task)
    {
        return $this->settingsResolver->getAgentChatTimeout();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableWorkers(Task $task, $ignoreRejected = false)
    {
        $this->logger->info(sprintf('[ChatWorkflow] Get available workers for the task, task_id = %s', $task->getId()));

        /** @var Worker[] $availableAgentWorkers */
        $availableAgentWorkers = [];
        foreach ($this->storage->getOnlineWorkersByType('agent') as $worker) {
            $availableAgentWorkers[$worker->getId()] = $worker;
        }

        $this->logger->info(sprintf(
            '[ChatWorkflow] Online agent workers = [%s], task_id = %s',
            implode(', ', array_keys($availableAgentWorkers)), $task->getId()
        ));

        $activeAgentIds        = $this->targetsLoader->getActiveAgentIdsForUserChat();
        $maxChatsCount         = $this->settingsResolver->getMaxChatsCount();
        $availableAgentWorkers = array_filter(
            $availableAgentWorkers,
            function (Worker $worker) use ($task, $maxChatsCount, $activeAgentIds, $ignoreRejected) {
                // ignore if agent has already rejected task
                if (!$ignoreRejected && $task->getRejectedBy() && in_array($worker->getId(), $task->getRejectedBy())) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker rejected the task, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                if ($worker->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker is busy, reason = has_pending_phone_call, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                if ($worker->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker is busy, reason = has_active_phone_call, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                $pendingChatTaskIds = $worker->getPendingTaskIdsForChannel(self::getChannelName());
                $activeChatTaskIds = $worker->getActiveTaskIdsForChannel(self::getChannelName());

                if (count($pendingChatTaskIds) > 0) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker is busy, reason = has_pending_chats, pending_chat_ids = [%s], active_chat_ids = [%s], worker_id = %s, task_id = %s',
                        implode(', ', $pendingChatTaskIds), implode(', ', $activeChatTaskIds), $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                if (count($activeChatTaskIds) >= $maxChatsCount) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker is busy, reason = max_chats_counts, pending_chat_ids = [%s], active_chat_ids = [%s], worker_id = %s, task_id = %s',
                        implode(', ', $pendingChatTaskIds), implode(', ', $activeChatTaskIds), $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                if (!in_array($worker->getTypeId(), $activeAgentIds)) {
                    $this->logger->info(sprintf(
                        '[ChatWorkflow] Worker is not available for chat, worker_id = %s, task_id = %s',
                        $worker->getTypeId(), $task->getId()
                    ));

                    return false;
                }

                $this->logger->info(sprintf(
                    '[ChatWorkflow] Available worker is found, worker_id = %s, task_id = %s',
                    $worker->getTypeId(), $task->getId()
                ));

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
        $chatQueue = $this->taskHelper->getChatQueue($task);
        if (!$chatQueue) {
            $this->logger->info(sprintf('[ChatWorkflow] No chat queue, skipping, task_id = %s', $task->getId()));

            return;
        }

        $this->logger->info(sprintf(
            '[ChatWorkflow] Selected chat queue for the task, queue_id = %s, task_id = %s',
            $chatQueue->getId(), $task->getId()
        ));

        // get available workers
        $workerToAgentMap = [];
        foreach ($workers as $worker) {
            $workerToAgentMap[$worker->getTypeId()] = $worker->getId();
        }

        $availableWorkerAgentIds = array_map(function (Worker $worker) {
            return $worker->getTypeId();
        }, $workers);

        $taskQueue = $this->storage->getTaskQueue(self::getChannelName(), $chatQueue->getId());
        if (!$taskQueue) {
            $taskQueue = new TaskQueue();
            $taskQueue->setType(self::getChannelName());
            $taskQueue->setTypeId($chatQueue->getId());
        }

        $targets = $this->targetsLoader->getChatQueueTargets($chatQueue);

        // check targets permissions
        /** @var AbstractUserChatQueueTarget[] $targets */
        $targets = array_filter($targets, function (AbstractUserChatQueueTarget $target) {
            return $this->permissionsChecker->canBeMemberOfChatQueue($target);
        });

        // sort targets by priority
        usort($targets, function (AbstractUserChatQueueTarget $target1, AbstractUserChatQueueTarget $target2) {
            return $target1->getSort() - $target2->getSort();
        });

        $this->logger->info(sprintf(
            '[ChatWorkflow] Routing model = %s, queue_id = %s, task_id = %s',
            $chatQueue->getRoutingModel(), $chatQueue->getId(), $task->getId()
        ));
        $this->logger->info(sprintf(
            '[ChatWorkflow] Filtered queue targets, queue_id = %s, target_ids = [%s], task_id = %s',
            $chatQueue->getId(), implode(', ',
            array_map(function (AbstractUserChatQueueTarget $target) {
                $targetInfo = $target->toArray();

                return 'type = '.$targetInfo['type'].', id = '.$targetInfo['id'];
            }, $targets)),
            $task->getId()
        ));

        switch ($chatQueue->getRoutingModel()) {
            case UserChatQueue::ROUTING_MODEL_ROUND_ROBIN:
                // set round robin order
                if (!is_array($taskQueue->getAttribute('round_robin_order'))) {
                    $order = [];
                    foreach ($targets as $target) {
                        $order[] = $target->toArray();
                    }

                    $taskQueue->setAttribute('round_robin_order', $order);
                }

                // iterate over task queue's round robin list to get first available worker
                // the first found one will be used as task consumer
                // and pushed to the end of the list so we will get the next one for the next task
                $order = $taskQueue->getAttribute('round_robin_order');
                if (is_array($order)) {
                    // check if 'round_robin_order' is up to date with chat queue agents list
                    foreach ($order as $num => $orderTarget) {
                        $hasTarget = false;
                        foreach ($targets as $queueTarget) {
                            if ($queueTarget->toArray() === $orderTarget) {
                                $hasTarget = true;
                            }
                        }
                        if (!$hasTarget) {
                            unset($order[$num]);
                        }
                    }

                    $order = array_values($order);
                    foreach ($targets as $queueTarget) {
                        $hasTarget = false;
                        foreach ($order as $orderTarget) {
                            if ($queueTarget->toArray() === $orderTarget) {
                                $hasTarget = true;
                            }
                        }
                        if (!$hasTarget) {
                            $order[] = $queueTarget->toArray();
                        }
                    }

                    // get next worker for the task
                    $order = array_values($order);
                    foreach ($order as $num => $orderTarget) {
                        $workersIds = [];
                        if ($orderTarget['type'] === AbstractUserChatQueueTarget::TYPE_AGENT) {
                            $agentId = $orderTarget['id'];
                            if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                                $workersIds[] = $workerToAgentMap[$agentId];
                            }
                        }

                        if ($workersIds) {
                            $task->setWorkersIds($workersIds);
                            $task->setDateExpireAssignedOffset($chatQueue->getAnswerTimeout());

                            // worker was fetched push to the end of the list
                            unset($order[$num]);
                            $order[] = $orderTarget;
                            $order   = array_values($order);

                            break;
                        }
                    }

                    $taskQueue->setAttribute('round_robin_order', $order);
                }

                break;
            case UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED:
                // get answered chats stat, order by answered chats count
                // and get ids with max available count of workers option from queue settings
                $answeredChatsCounts = $taskQueue->getAttribute('answered_chats_counts');
                if (!is_array($answeredChatsCounts)) {
                    $answeredChatsCounts = [];
                }

                // check if 'answered_calls_counts' is up to date with voice queue agents list
                foreach ($answeredChatsCounts as $num => $chatCount) {
                    $hasTarget = false;
                    foreach ($targets as $queueTarget) {
                        if ($chatCount['target'] === $queueTarget->toArray()) {
                            $hasTarget = true;
                        }
                    }
                    if (!$hasTarget) {
                        unset($answeredChatsCounts[$num]);
                    }
                }
                foreach ($targets as $queueTarget) {
                    $hasTarget = false;
                    foreach ($answeredChatsCounts as $chatCount) {
                        if ($chatCount['target'] === $queueTarget->toArray()) {
                            $hasTarget = true;
                        }
                    }
                    if (!$hasTarget) {
                        $answeredChatsCounts[] = [
                            'target' => $queueTarget->toArray(),
                            'count'  => 0,
                        ];
                    }
                }

                // sort by least utilized
                // and return limited by max allowed workers number
                usort($answeredChatsCounts, function ($count1, $count2) {
                    return $count1['count'] - $count2['count'];
                });

                $workerIds = [];
                foreach ($answeredChatsCounts as $chatCount) {
                    $targetType = $chatCount['target']['type'];
                    if ($targetType === AbstractUserChatQueueTarget::TYPE_AGENT) {
                        $agentId = $chatCount['target']['id'];
                        if (isset($workerToAgentMap[$agentId])) {
                            $workerIds[] = $workerToAgentMap[$agentId];

                            if (count($workerIds) >= $chatQueue->getMaxQueueSize()) {
                                break;
                            }
                        }
                    }
                }

                $task->setWorkersIds($workerIds);
                $task->setDateExpireAssignedOffset($chatQueue->getAnswerTimeout());

                break;
            case UserChatQueue::ROUTING_MODEL_SIMULRING:
                $workersIds = [];

                foreach ($targets as $target) {
                    if ($target instanceof UserChatQueueAgent) {
                        $agentId = $target->getAgent()->getId();

                        if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                            $workersIds[] = $workerToAgentMap[$agentId];
                        }
                    }
                }

                $task->setWorkersIds($workersIds);

                break;
        }

        $this->storage->saveTaskQueue($taskQueue);
    }
}
