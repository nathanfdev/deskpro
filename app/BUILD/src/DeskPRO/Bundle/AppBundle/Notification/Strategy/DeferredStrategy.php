<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;

/**
 * Class DeferredStrategy
 * The cloud strategy is simple: just persist LegacySystemEvent, and then CloudService started in cron-job should
 * calculate messages and targets, then it should deliver.
 */
class DeferredStrategy extends AbstractStrategy
{
    /**
     * @param SystemEventInterface $event
     */
    public function handleSystemEvent(SystemEventInterface $event)
    {
        $this->persistEvent($event);
    }

    /**
     * @param SystemEventInterface $event
     */
    public function handlePersistedEvent(SystemEventInterface $event)
    {
        $messages = $this->createMessages($event);
        foreach ($messages as $message) {
            $this->deliveryService->schedule($message);
        }
        $this->deliveryService->deliver();
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
     * @return array
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
     * @param PersistenceAdapterInterface $persistenceAdapter
     *
     * @return DeferredStrategy
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $persistenceAdapter)
    {
        $this->persistenceAdapter = $persistenceAdapter;

        return $this;
    }

    /**
     * @param $event
     */
    protected function persistEvent($event)
    {
        $this->persistenceAdapter->persist($event);
    }
}
