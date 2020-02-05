<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;

/**
 * Class ChatTypingGenerator.
 */
class ChatTypingGenerator extends ChatGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent && (
                $event->getType() === ChatEvent::TYPING_START_EVENT_TYPE ||
                $event->getType() === ChatEvent::TYPING_END_EVENT_TYPE
            );
    }

    /**
     * @param \DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        $chat    = $this->getChat($event);
        $data    = $event->getData();
        $isAgent = $data['origin'] === 'agent';

        if ($isAgent) {
            $agent  = $chat->getAgent();
            $avatar = $this->avatarResolver->getAvatar($agent);
            $name   = null !== $agent ? $agent->getDisplayNameUser() : 'agent';
        } else {
            $avatar = $chat->getPersonPictureUrl();
            $name   = $chat->getPersonName() ?: $chat->getPersonEmail() ?: 'user';
        }

        $message = [
            'chat'        => $chat->getId(),
            'origin'      => $data['origin'],
            'name'        => $name,
            'avatar'      => $avatar,
            'date_typing' => isset($data['date_typing']) && $data['date_typing'] instanceof \DateTime
                ? $data['date_typing']->format(\DateTime::ISO8601)
                : date(\DateTime::ISO8601, time()),
        ];

        if (!$isAgent) {
            $message['message'] = $data['message'];
        }

        return $message;
    }
}
