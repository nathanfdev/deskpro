<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;

/**
 * Class ChatUserJoinedLeftGenerator.
 */
class ChatUserJoinedLeftGenerator extends ChatGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent && (
            $event->getType() === ChatEvent::CHAT_USER_JOINED_EVENT_TYPE
            || $event->getType() === ChatEvent::CHAT_USER_LEFT_EVENT_TYPE
        );
    }

    /**
     * @param ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        $chat      = $this->getChat($event);
        $eventData = $event->getData();

        $data = [
            'origin' => 'system',
            'avatar' => $chat->getAgent()
                ? $this->avatarResolver->getAvatar($chat->getAgent())
                : $this->avatarResolver->getDefaultPersonAvatar(),
        ];

        if (isset($eventData['message']) && $eventData['message'] instanceof ChatMessage) {
            $key = $event->getType() === ChatEvent::CHAT_USER_JOINED_EVENT_TYPE ? 'date_joined' : 'date_left';
            $data += $this->chatMapper->mapMessageToArray($eventData['message']);
            $messageData[$key] = $eventData['message']->getDateCreated()->format(\DateTime::ISO8601);
        }

        return $data;
    }
}
