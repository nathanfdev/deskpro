<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AgentChatMessageListener.
 */
class AgentChatMessageListener
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param AgentChatMessage $entity
     */
    public function postPersist(AgentChatMessage $entity)
    {
        $this->eventDispatcher->dispatch(NewMessageEvent::EVENT_NAME, new NewMessageEvent($entity->getId()));
    }
}
