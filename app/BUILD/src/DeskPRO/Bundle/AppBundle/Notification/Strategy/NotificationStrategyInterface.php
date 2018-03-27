<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Strategy;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryService;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotifyHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Persistance\PersistenceAdapterInterface;

/**
 * Interface NotificationStrategyInterface.
 */
interface NotificationStrategyInterface
{
    /**
     * @param SystemEventInterface $event
     */
    public function handleSystemEvent(SystemEventInterface $event);

    /**
     * @param SystemEventInterface $event
     */
    public function handlePersistedEvent(SystemEventInterface $event);

    /**
     * @param DeliveryService $deliveryService
     *
     * @return NotificationStrategyInterface
     */
    public function setDeliveryService(DeliveryService $deliveryService);

    /**
     * @param NotifyHandlerInterface $handler
     *
     * @return NotificationStrategyInterface
     */
    public function attachEventHandler(NotifyHandlerInterface $handler);

    /**
     * @param PersistenceAdapterInterface $persistance_adapter
     *
     * @return NotificationStrategyInterface
     */
    public function setPersistenceAdapter(PersistenceAdapterInterface $persistance_adapter);

    public function startBatch();

    public function resetBatch();

    public function stopBatch();

    /**
     * @param bool $postpone
     *
     * @return mixed
     */
    public function deliver($postpone = false);
}
