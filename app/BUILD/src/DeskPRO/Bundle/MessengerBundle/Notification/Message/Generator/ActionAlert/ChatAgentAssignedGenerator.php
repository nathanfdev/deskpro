<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;

/**
 * Class ChatAgentAssignedGenerator.
 */
class ChatAgentAssignedGenerator extends ChatGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent && $event->getType() === ChatEvent::CHAT_AGENT_ASSIGNED_EVENT_TYPE;
    }

    /**
     * @param ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        $chat = $this->getChat($event);

        return [
            'id'            => $chat->getId(),
            'origin'        => 'system',
            'name'          => $chat->getAgent()->getDisplayNameUser(),
            'avatar'        => $this->avatarResolver->getAvatar($chat->getAgent()),
            'date_assigned' => $chat->getDateAssigned()->format(\DateTime::ISO8601),
        ];
    }
}
