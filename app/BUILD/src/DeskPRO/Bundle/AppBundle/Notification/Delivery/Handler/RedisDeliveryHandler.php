<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use Predis\Client;

/**
 * Class RedisDeliveryHandler.
 */
class RedisDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.redis';

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
}
