<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener;

use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param ChatTaskHelper           $taskHelper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ChatTaskHelper $taskHelper, EventDispatcherInterface $dispatcher)
    {
        $this->taskHelper = $taskHelper;
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
