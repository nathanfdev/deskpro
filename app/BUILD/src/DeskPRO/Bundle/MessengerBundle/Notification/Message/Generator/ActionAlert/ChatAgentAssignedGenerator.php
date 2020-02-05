<?php

namespace DeskPRO\Bundle\MessengerBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Person;
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
        return $event instanceof ChatEvent && (
            $event->getType() === ChatEvent::CHAT_AGENT_ASSIGNED_EVENT_TYPE
            || $event->getType() === ChatEvent::CHAT_AGENT_UNASSIGNED_EVENT_TYPE
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
        ];

        $agent = null;

        if ($chat->getAgent()) {
            $agent = $chat->getAgent();
        } elseif (isset($eventData['agent'])) {
            $agent = $this->em->find(Person::class, $eventData['agent']);
        }

        if ($agent) {
            $data['name']   = $agent->getDisplayNameUser();
            $data['avatar'] = $this->avatarResolver->getAvatar($agent);
        }

        if (isset($eventData['message']) && $eventData['message'] instanceof ChatMessage) {
            $data += $this->chatMapper->mapMessageToArray($eventData['message']);
        }

        if ($event->getType() === ChatEvent::CHAT_AGENT_ASSIGNED_EVENT_TYPE) {
            $data['date_assigned'] = $chat->getDateAssigned()->format(\DateTime::ISO8601);
        }

        return $data;
    }
}
