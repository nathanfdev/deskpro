<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgentTeam;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class ChatWorkflow.
 */
class ChatWorkflow implements WorkflowInterface
{
    /**
     * @var EntityManager
     */
    private $em;

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
     * Constructor.
     *
     * @param EntityManager           $em
     * @param ChatTaskHelper          $taskHelper
     * @param ChatSettingsResolver    $settingsResolver
     * @param StorageAdapterInterface $storage
     */
    public function __construct(
        EntityManager           $em,
        ChatTaskHelper          $taskHelper,
        ChatSettingsResolver    $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $this->em               = $em;
        $this->taskHelper       = $taskHelper;
        $this->settingsResolver = $settingsResolver;
        $this->storage          = $storage;
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
    public function isTaskTimedOut(Task $task)
    {
        $now = new \DateTime();

        $timeout = $this->settingsResolver->getAgentChatTimeout();
        $offset  = $now->getTimestamp() - $task->getDateCreated()->getTimestamp();

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

        $activeAgentIds        = $this->getPersonRepo()->getActiveAgentIdsForUserChat();
        $maxChatsCount         = $this->settingsResolver->getMaxChatsCount();
        $availableAgentWorkers = array_filter(
            $availableAgentWorkers,
            function (Worker $worker) use ($task, $maxChatsCount, $activeAgentIds) {
                // ignore if agent has already rejected task
                if ($task->getRejectedBy() && in_array($worker->getId(), $task->getRejectedBy())) {
                    return false;
                }

                // ignore if agent is already on a call or has incoming call popup
                if ($worker->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())
                    || $worker->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())
                    || $worker->hasPendingTasksForChannel(self::getChannelName())
                    || count($worker->getActiveTaskIdsForChannel(self::getChannelName())) >= $maxChatsCount
                    || !in_array($worker->getTypeId(), $activeAgentIds)
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
        $chatQueue = $this->taskHelper->getChatQueue($task);
        if (!$chatQueue) {
            return;
        }

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

        if ($chatQueue->isAllAgents()) {
            // all agents are available so dynamically create chat queue targets
            // based on the agents list
            $targets = [];

            $agentIds = $this->getPersonRepo()->getActiveAgentIdsForUserChat();
            $agents   = $this->getPersonRepo()->findBy([
                'id' => $agentIds,
            ]);

            foreach ($agents as $agent) {
                $target = new UserChatQueueAgent();
                $target->setAgent($agent);

                $targets[] = $target;
            }
        } else {
            $targets = $chatQueue->getTargets();
        }

        // sort targets by priority
        usort($targets, function (AbstractUserChatQueueTarget $target1, AbstractUserChatQueueTarget $target2) {
            return $target1->getSort() - $target2->getSort();
        });

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
                        } elseif ($orderTarget['type'] === AbstractUserChatQueueTarget::TYPE_AGENT_TEAM) {
                            $agentTeam = $this->em->getRepository(AgentTeam::class)->find($orderTarget['id']);
                            if ($agentTeam instanceof AgentTeam) {
                                foreach ($agentTeam->getMembers() as $member) {
                                    $agentId = $member->getId();
                                    if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                                        $workersIds[] = $workerToAgentMap[$agentId];
                                    }
                                }
                            }
                        }

                        if ($workersIds) {
                            $task->setWorkersIds($workersIds);
                            $task->setDateExpireOffset($chatQueue->getAnswerTimeout());

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
                    } elseif ($targetType === AbstractUserChatQueueTarget::TYPE_AGENT_TEAM) {
                        $agentTeam = $this->em->getRepository(AgentTeam::class)->find($chatCount['target']['id']);
                        if ($agentTeam instanceof AgentTeam) {
                            foreach ($agentTeam->getMembers() as $member) {
                                $agentId = $member->getId();
                                if (isset($workerToAgentMap[$agentId])) {
                                    $workerIds[] = $workerToAgentMap[$agentId];
                                }

                                if (count($workerIds) >= $chatQueue->getMaxQueueSize()) {
                                    break;
                                }
                            }
                        }
                    }
                }

                $task->setWorkersIds($workerIds);
                $task->setDateExpireOffset($chatQueue->getAnswerTimeout());

                break;
            case UserChatQueue::ROUTING_MODEL_SIMULRING:
                $workersIds = [];

                foreach ($targets as $target) {
                    if ($target instanceof UserChatQueueAgent) {
                        $agentId = $target->getAgent()->getId();

                        if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                            $workersIds[] = $workerToAgentMap[$agentId];
                        }
                    } elseif ($target instanceof UserChatQueueAgentTeam) {
                        foreach ($target->getAgentTeam()->getMembers() as $member) {
                            $agentId = $member->getId();
                            if (in_array($agentId, $availableWorkerAgentIds) && isset($workerToAgentMap[$agentId])) {
                                $workersIds[] = $workerToAgentMap[$agentId];
                            }
                        }
                    }
                }

                $task->setWorkersIds($workersIds);
                $task->setDateExpireOffset($chatQueue->getAnswerTimeout());

                break;
        }

        $this->storage->saveTaskQueue($taskQueue);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|\Application\DeskPRO\EntityRepository\Person
     */
    protected function getPersonRepo()
    {
        return $this->em->getRepository(Person::class);
    }
}
