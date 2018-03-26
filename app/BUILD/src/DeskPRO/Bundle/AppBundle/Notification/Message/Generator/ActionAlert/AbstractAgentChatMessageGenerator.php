<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\AbstractMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;

/**
 * Class AbstractAgentChatMessageGenerator.
 */
abstract class AbstractAgentChatMessageGenerator extends AbstractGenerator
{
    /**
     * @param AbstractMessageEvent $event
     *
     * @return Person[]
     */
    protected function getTargets(AbstractMessageEvent $event)
    {
        $chat = $this->getChatMessage($event)->getChat();
        if ($chat->getType() === AgentChat::TYPE_EVERYONE) {
            return $this->em->getRepository(Person::class)->findBy(['is_agent' => true]);
        }

        return $chat->getPersonList();
    }

    /**
     * @param AbstractMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getChatMessage(AbstractMessageEvent $event)
    {
        $message = $this->em->getRepository(AgentChatMessage::class)->findOneBy(['id' => $event->getMessageId()]);
        if (!$message) {
            throw new \InvalidArgumentException(sprintf('No message with id [ %s ] was found!', $event->getMessageId()));
        }

        return $message;
    }
}
