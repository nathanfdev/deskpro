<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert\Messenger;

use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;

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
     * @param ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        $chat    = $this->getChat($event);
        $data    = $event->getData();
        $isAgent = $data['origin'] === 'agent';

        $avatar = $isAgent ? $this->avatarResolver->getAvatar($chat->getAgent()) : $chat->getPersonPictureUrl();

        return [
            'origin'      => $data['origin'],
            'name'        => $isAgent ? $chat->getAgent()->getDisplayNameUser() : $chat->getPersonName() ?: $chat->getPersonEmail() ?: 'user',
            'avatar'      => $avatar,
            'date_typing' => isset($data['date_typing']) && $data['date_typing'] instanceof \DateTime
                ? $data['date_typing']->format(\DateTime::ISO8601)
                : date(\DateTime::ISO8601, time()),
        ];
    }
}
