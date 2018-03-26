<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;

/**
 * Class ImmediateStrategy.
 */
class ImmediateStrategy extends AbstractStrategy
{
    /**
     * @param SystemEventInterface $event
     */
    public function handleSystemEvent(SystemEventInterface $event)
    {
        $messages = $this->createMessages($event);
        foreach ($messages as $message) {
            $this->deliveryService->schedule($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deliver($postpone = false)
    {
        $this->deliveryService->deliver($postpone);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    protected function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->eventHandlers as $handler) {
            /** @var NotifyHandlerInterface $handler */
            $messages = array_merge($messages, $handler->processEvent($event));
        }

        return $messages;
    }

    /**
     * For immediate strategy it's just a stub - no need to persist anything.
     *
     * @param PersistenceAdapterInterface $persistenceAdapter
     *
     * @return $this
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $persistenceAdapter)
    {
        return $this;
    }
}
