<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;

/**
 * Class ChatEndedGenerator.
 */
class ChatEndedGenerator extends ChatGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent && (
            $event->getType() === ChatEvent::CHAT_ENDED_EVENT_TYPE
            || $event->getType() === ChatEvent::CHAT_WAIT_TIMEOUT_EVENT_TYPE
            || $event->getType() === ChatEvent::CHAT_USER_TIMEOUT_EVENT_TYPE
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

        switch ($chat->getEndedBy()) {
            case ChatConversation::ENDED_TIMEOUT:
            case ChatConversation::ENDED_WAIT_TIMEOUT:
                $origin = 'system';
                break;
            case ChatConversation::ENDED_ABANDONED:
            case ChatConversation::ENDED_AGENT:
                $origin = 'agent';
                break;
            case ChatConversation::ENDED_USER:
                $origin = 'user';
                break;
            default:
                $origin = 'system';
        }

        switch ($origin) {
            case 'agent':
                $avatar = $chat->getAgent()
                    ? $this->avatarResolver->getAvatar($chat->getAgent())
                    : $this->avatarResolver->getDefaultPersonAvatar();
                break;
            case 'user':
                $avatar = $chat->getPersonPictureUrl();
                break;
            default:
                $avatar = $this->avatarResolver->getDefaultCommonAvatar();
        }

        $messageData = [];
        if (isset($eventData['message']) && $eventData['message'] instanceof ChatMessage) {
            $messageData = $this->chatMapper->mapMessageToArray($eventData['message']);
        }

        return $messageData + [
            'chat'       => $chat->getId(),
            'origin'     => $origin,
            'avatar'     => $avatar,
            'date_ended' => $chat->getDateEnded()->format(\DateTime::ISO8601),
        ];
    }
}
