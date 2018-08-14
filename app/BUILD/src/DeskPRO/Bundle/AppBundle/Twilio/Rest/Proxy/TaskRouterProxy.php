<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Taskrouter;

/**
 * Class TaskRouterProxy.
 */
class TaskRouterProxy extends Taskrouter
{
    /**
     * {@inheritdoc}
     *
     * @param ClientProxy $client
     */
    public function __construct(ClientProxy $client)
    {
        parent::__construct($client);

        if ($client->getTaskRouterProxyUrl()) {
            $this->baseUrl = $client->getTaskRouterProxyUrl();
        }
    }
}
