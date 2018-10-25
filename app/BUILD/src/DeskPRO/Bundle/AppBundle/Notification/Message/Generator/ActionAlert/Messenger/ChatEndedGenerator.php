<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert\Messenger;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;

/**
 * Class ChatEndedGenerator.
 */
class ChatEndedGenerator extends ChatGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return array
     */
    protected function getTargets(SystemEventInterface $event)
    {
        /** @var ChatEvent $event */
        $chat           = $this->getChat($event);
        $agentTargets   = $chat->getAgentParticipants() ?: [];
        $userTargets    = $chat->getUserParticipants() ?: [];
        $visitorTargets = $chat->getVisitorId() ?: '';

        return array_merge($agentTargets, $userTargets, [$visitorTargets]);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return ChatConversation
     */
    protected function getChat(SystemEventInterface $event)
    {
        /** @var ChatEvent $event */
        $repository = $this->em->getRepository(ChatConversation::class);

        return $repository->find($event->getChatId());
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent && $event->getType() === ChatEvent::CHAT_ENDED_EVENT_TYPE;
    }

    /**
     * @param ChatEvent $event
     *
     * @return array
     */
    protected function getData(ChatEvent $event)
    {
        $chat = $this->getChat($event);

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
                $origin = 'agent';
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

        return [
            'origin'     => $origin,
            'avatar'     => $avatar,
            'date_ended' => $chat->getDateEnded()->format(\DateTime::ISO8601),
        ];
    }
}
