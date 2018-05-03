<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\AbstractMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;

/**
 * Class ReadNotificationsMessageGenerator.
 */
class ReadNotificationsMessageGenerator extends SystemEventGenerator
{
    /**
     * This is just a little optimization - we're about to alert client only once to read notifications
     * So if target is here - be sure they notified about notifications (omg).
     *
     * @var array
     */
    private $targets;

    /**
     * {@inheritdoc}
     *
     * @param NewMessageEvent $event
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new ActionAlert($target, [], 'read.notifications.alert');
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof NewMessageEvent || $event instanceof UserChatEvent;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return Person[]
     */
    protected function getTargets(SystemEventInterface $event)
    {
        $targets = [];
        if ($event instanceof NewMessageEvent) {
            $targets = $this->getChatTargets($event);
        } elseif ($event instanceof UserChatEvent) {
            $targets = $this->getUserChatTargets($event);
        }

        $processedTargets = $this->targets;

        $targets = array_filter($targets, function ($target) use ($processedTargets) {
            $targetId = $target instanceof Person ? $target->getId() : $target;

            return !isset($processedTargets[$targetId]);
        });

        foreach ($targets as &$target) {
            $targetId                 = $target instanceof Person ? $target->getId() : $target;
            $this->targets[$targetId] = true;
            $target                   = $targetId;
        }

        return $targets;
    }

    /**
     * @param AbstractMessageEvent $event
     *
     * @return \Application\DeskPRO\Entity\Person[]
     */
    protected function getChatTargets(AbstractMessageEvent $event)
    {
        $agentChatMessage = $this->getChatMessage($event);
        $chat             = $agentChatMessage->getChat();

        $filterFunction = function ($target) use ($agentChatMessage) {
            /* @var Person $target */
            return $target->getId() !== $agentChatMessage->getPerson()->getId();
        };

        if ($chat->getType() === AgentChat::TYPE_EVERYONE) {
            return array_filter($this->em->getRepository(Person::class)->findBy([
                'is_agent'    => true,
                'is_deleted'  => false,
                'is_disabled' => false,
            ]), $filterFunction);
        }

        return array_filter($chat->getPersonList(), $filterFunction);
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

    protected function getUserChatTargets(UserChatEvent $event)
    {
        return $this->getTarget($event);
    }
}
