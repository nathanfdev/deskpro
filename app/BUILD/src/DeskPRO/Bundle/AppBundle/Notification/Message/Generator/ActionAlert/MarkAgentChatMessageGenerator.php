<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;

/**
 * Class MarkAgentChatMessageGenerator.
 */
class MarkAgentChatMessageGenerator extends AbstractAgentChatMessageGenerator
{
    /**
     * {@inheritdoc}
     *
     * @param MarkMessageEvent $event
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            if ($target !== $this->getUser()->getId()) {
                $messages[] = new ActionAlert($target->getId(), $this->getData($event), $event->getName());
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof MarkMessageEvent;
    }

    /**
     * @param MarkMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(MarkMessageEvent $event)
    {
        $agentChatMessage = $this->getChatMessage($event);

        return [
            'message_id'   => $event->getMessageId(),
            'person'       => $agentChatMessage->getPerson()->getId(),
            'message_uuid' => $agentChatMessage->getUuid(),
            'status'       => $event->getStatus(),
            'chat'         => $agentChatMessage->getChat()->getId(),
        ];
    }
}
