<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent as ChatNotificationEvent;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use JMS\Serializer\Serializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ChatAgentNotifyListener.
 */
class ChatAgentNotifyListener implements EventSubscriberInterface
{
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
     * @param ChatTaskHelper           $taskHelper
     * @param Serializer               $serializer
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ChatTaskHelper           $taskHelper,
        Serializer               $serializer,
        StorageAdapterInterface  $storage,
        EventDispatcherInterface $dispatcher
    ) {
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
            TaskRouterEvent::ASSIGNED => 'onAssigned',
            TaskRouterEvent::CANCELED => 'onCanceled',
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

        $data = array_merge(
            $this->serializer->toArray($chat, new SideloadSerializationContext()),
            [
                'conversation_id' => $chat->getId(),
                'target'          => array_map(function (Worker $worker) {
                    return $worker->getTypeId();
                }, $this->storage->getWorkers($task->getWorkerIds())),
            ]
        );

        $this->dispatcher->dispatch(UserChatEvent::STARTED, new UserChatEvent($chat));
        $this->dispatcher->dispatch(ChatNotificationEvent::EVENT_NAME, new ChatNotificationEvent('chat.new', $data));
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
