<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert\Messenger;

use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;

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
