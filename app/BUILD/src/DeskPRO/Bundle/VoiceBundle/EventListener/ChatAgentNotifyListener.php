<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent as ChatNotificationEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ChatAgentNotifyListener.
 */
class ChatAgentNotifyListener implements EventSubscriberInterface
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
     * @var Serializer
     */
    private $serializer;

    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param ChatTaskHelper           $taskHelper
     * @param Serializer               $serializer
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManager            $em,
        ChatTaskHelper           $taskHelper,
        Serializer               $serializer,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em         = $em;
        $this->taskHelper = $taskHelper;
        $this->serializer = $serializer;
        $this->storage    = $storage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskRouterEvent::ASSIGNED      => 'onAssigned',
            TaskRouterEvent::ACCEPTED      => 'onAccepted',
            TaskRouterEvent::TASK_CANCELED => 'onCanceled',
        ];
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onAssigned(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $chat = $this->taskHelper->getChat($task);
        if (!$chat) {
            return;
        }

        $chatQueue = $this->taskHelper->getChatQueue($task);
        if ($chatQueue->getRoutingModel() === UserChatQueue::ROUTING_MODEL_ROUND_ROBIN) {
            // if it's round robin then assign the chat directly to the agent
            if ($task->getWorkerIds()) {
                $worker = $this->storage->getWorker($task->getWorkerIds()[0]);
                if ($worker) {
                    $agent = $this->em->getRepository(Person::class)->find($worker->getTypeId());
                    if ($agent) {
                        $chat->setAgent($agent);
                        $this->em->flush();

                        $this->dispatcher->dispatch(UserChatEvent::STARTED, new UserChatEvent($chat));
                        $this->dispatcher->dispatch(UserChatEvent::ASSIGNED, new UserChatEvent($chat, [
                            'name' => $chat->getAgent()->getDisplayName(),
                        ]));
                    }
                }
            }
        }

        $data = array_merge(
            $this->serializer->toArray($chat, new SideloadSerializationContext()),
            [
                'conversation_id' => $chat->getId(),
                'target'          => array_map(function (Worker $worker) {
                    return $worker->getTypeId();
                }, $this->storage->getWorkers($task->getWorkerIds())),
            ]
        );

        $this->dispatcher->dispatch(ChatNotificationEvent::EVENT_NAME, new ChatNotificationEvent('chat.new', $data));
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

        $chat = $this->taskHelper->getChat($task);
        if (!$chat) {
            return;
        }

        $this->dispatcher->dispatch(UserChatEvent::STARTED, new UserChatEvent($chat));
    }

    /**
     * @internal
     *
     * @param TaskRouterEvent $event
     */
    public function onCanceled(TaskRouterEvent $event)
    {
        $task = $event->getTask();
        if (!$task) {
            return;
        }

        $chat = $this->taskHelper->getChat($task);
        if (!$chat) {
            return;
        }

        $this->dispatcher->dispatch(UserChatEvent::END_BY_USER, new UserChatEvent($chat, [], ['chat_ended']));
    }
}
