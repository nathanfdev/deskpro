<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerCollection;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;

abstract class AbstractStrategy implements NotificationStrategyInterface
{
    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var NotifyHandlerCollection
     */
    protected $eventHandlers;

    /**
     * @var PersistenceAdapterInterface
     */
    protected $persistenceAdapter;

    /**
     * AbstractStrategy constructor.
     */
    public function __construct()
    {
        $this->eventHandlers = new NotifyHandlerCollection();
    }

    /**
     * @param DeliveryService $deliveryService
     *
     * @return $this
     */
    public function setDeliveryService(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;

        return $this;
    }

    /**
     * @param NotifyHandlerInterface $handler
     *
     * @return $this;
     */
    public function attachEventHandler(NotifyHandlerInterface $handler)
    {
        $this->eventHandlers->attachHandler($handler);

        return $this;
    }

    /**
     * @param SystemEventInterface $event
     */
    public function handlePersistedEvent(SystemEventInterface $event)
    {
        // this is just a stub for edge case when someone switched deferred strategy to immediate
        // and factory returns immediate strategy, that couldn't process persisted events
        return;
    }

    /**
     * @return mixed
     */
    abstract public function deliver($postpone = false);

    /**
     * @return $this
     */
    public function startBatch()
    {
        $this->deliveryService->startBatch();

        return $this;
    }

    /**
     * @return $this
     */
    public function resetBatch()
    {
        $this->deliveryService->resetBatch();

        return $this;
    }

    /**
     * @return $this
     */
    public function stopBatch()
    {
        $this->deliveryService->stopBatch();

        return $this;
    }
}
