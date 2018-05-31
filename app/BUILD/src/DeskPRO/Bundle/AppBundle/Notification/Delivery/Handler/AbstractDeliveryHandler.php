<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Delivery\DeliveryHandlerInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;

/**
 * Class AbstractDeliveryHandler.
 */
abstract class AbstractDeliveryHandler implements DeliveryHandlerInterface
{
    const TYPE = 'notification.delivery.handler.abstract';

    const MAX_MESSAGE_SIZE = 10240; // 10Kb for pusherapp;

    const CHANNEL_ACTION_ALERT = 'action_alert';
    const CHANNEL_USER_NOTIFY  = 'user_notify';

    /**
     * @var int
     */
    protected $tries;

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
    protected function getChannel(MessageInterface $message)
    {
        if ($message instanceof ActionAlert) {
            return static::CHANNEL_ACTION_ALERT;
        } elseif ($message instanceof Notification) {
            return static::CHANNEL_USER_NOTIFY;
        }

        throw new \InvalidArgumentException('Message should be ActionAlert or Notification');
    }

    /**
     * {@inheritdoc}
     */
    public function deliverSoon()
    {
        $this->deliver();
    }
}
