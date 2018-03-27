<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

/**
 * Class AbstractDeliveryHandler.
 */
abstract class AbstractDeliveryHandler implements DeliveryHandlerInterface
{
    const TYPE = 'notification.delivery.handler.abstract';

    const MAX_MESSAGE_SIZE = 10240; // 10Kb for pusherapp;

    /**
     * @return string
     */
    public function getType()
    {
        if (self::TYPE === static::TYPE) {
            throw new \LogicException('You should override DeliveryHandler TYPE constant to attach it. Can\'t attach abstract type');
        }

        return static::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function getChannel(MessageInterface $message);

    /**
     * {@inheritdoc}
     */
    public function deliverSoon()
    {
        $this->deliver();
    }
}
