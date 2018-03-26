<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

/**
 * Interface DeliveryHandlerInterface.
 */
interface DeliveryHandlerInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param MessageInterface $message
     *
     * @return bool
     */
    public function schedule(MessageInterface $message);

    /**
     * @return mixed
     */
    public function deliver();

    public function deliverSoon();
}
