<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgentTeam;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ChatQueueCountListener.
 */
class ChatQueueCountListener implements EventSubscriberInterface
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
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * Constructor.
     *
     * @param EntityManager           $em
     * @param ChatTaskHelper          $taskHelper
     * @param StorageAdapterInterface $storage
     */
    public function __construct(EntityManager $em, ChatTaskHelper $taskHelper, StorageAdapterInterface $storage)
    {
        $this->em         = $em;
        $this->taskHelper = $taskHelper;
        $this->storage    = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ACCEPTED => 'onAccepted',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onAccepted(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $chatQueue = $this->taskHelper->getChatQueue($task);
        if (!$chatQueue) {
            return;
        }

        // if the call came from a chat queue
        // then update count stat to handle routing model strategies
        $taskQueue = $this->storage->getTaskQueue(ChatWorkflow::getChannelName(), $chatQueue->getId());
        if (!$taskQueue) {
            return;
        }

        $chatsCounts = $taskQueue->getAttribute('answered_chats_counts') ?: [];
        $worker      = $this->storage->getWorker($task->getAcceptedWorkerId());

        if (!$worker) {
            return;
        }

        $agent = $this->em->getRepository(Person::class)->find($worker->getTypeId());
        if (!$agent) {
            return;
        }

        $incrementTargetCount = function (AbstractUserChatQueueTarget $target) use (&$chatsCounts) {
            $hasTarget = false;
            foreach ($chatsCounts as &$chatsCount) {
                if ($chatsCount['target'] === $target->toArray()) {
                    ++$chatsCount['count'];

                    $hasTarget = true;
                }
            }

            if (!$hasTarget) {
                $chatsCounts[] = [
                    'target' => $target->toArray(),
                    'count'  => 1,
                ];
            }
        };

        if ($chatQueue->isAllAgents()) {
            $target = new UserChatQueueAgent();
            $target->setAgent($agent);

            $incrementTargetCount($target);
        } else {
            foreach ($chatQueue->getTargets() as $target) {
                if ($target instanceof UserChatQueueAgent) {
                    if ($target->getAgent() === $agent) {
                        $incrementTargetCount($target);
                    }
                } elseif ($target instanceof UserChatQueueAgentTeam) {
                    foreach ($target->getAgentTeam()->getMembers() as $member) {
                        if ($member === $agent) {
                            $incrementTargetCount($target);
                        }
                    }
                }
            }
        }

        $taskQueue->setAttribute('answered_chats_counts', $chatsCounts);

        $this->storage->saveTaskQueue($taskQueue);
    }
}
