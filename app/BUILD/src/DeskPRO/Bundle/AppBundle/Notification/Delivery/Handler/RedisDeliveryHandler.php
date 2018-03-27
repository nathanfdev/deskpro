<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use Predis\Client;

/**
 * Class RedisDeliveryHandler.
 */
class RedisDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.redis';

    const CHANNEL_ACTION_ALERT = 'action_alert';
    const CHANNEL_USER_NOTIFY  = 'user_notify';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->client->connect();
    }

    /**
     * @param MessageInterface $message
     */
    public function schedule(MessageInterface $message)
    {
        //this is particular message should be sent only throught db client
        if ($message->getType() === 'read.notifications.alert') {
            return;
        }

        $data = json_encode(
            [
                'target' => $message->getTarget(),
                'date'   => $message->getDate(),
                'id'     => $message->getId(),
                'type'   => $message->getType(),
            ] + $message->getData()
        );

        $this->messages[] = ['channel' => $this->getChannel($message), 'data' => $data];
    }

    public function deliver()
    {
        foreach ($this->messages as $message) {
            $this->client->publish($message['channel'], $message['data']);
        }
        $this->messages = [];
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    protected function getChannel(MessageInterface $message)
    {
        if ($message instanceof ActionAlert) {
            return self::CHANNEL_ACTION_ALERT;
        } elseif ($message instanceof Notification) {
            return self::CHANNEL_USER_NOTIFY;
        }

        throw new \InvalidArgumentException('Message should be ActionAlert or Notification');
    }
}
