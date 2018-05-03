<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkAllMessagesEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;

/**
 * Class MarkAgentChatMessageGenerator.
 */
class MarkAllAgentChatMessagesGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     *
     * @param MarkAllMessagesEvent $event
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
        return $event instanceof MarkAllMessagesEvent;
    }

    /**
     * @param MarkAllMessagesEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(MarkAllMessagesEvent $event)
    {
        return [
            'chatId' => $event->getChatId(),
        ];
    }

    /**
     * @param MarkAllMessagesEvent $event
     *
     * @return Person[]
     */
    protected function getTargets(MarkAllMessagesEvent $event)
    {
        $chat = $message = $this->em->getRepository(AgentChat::class)->find($event->getChatId());
        if ($chat->getType() === AgentChat::TYPE_EVERYONE) {
            return $this->em->getRepository(Person::class)->findBy(['is_agent' => true]);
        }

        return $chat->getPersonList();
    }
}
