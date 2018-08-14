<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Api;

/**
 * Class ApiProxy.
 */
class ApiProxy extends Api
{
    /**
     * {@inheritdoc}
     *
     * @param ClientProxy $client
     */
    public function __construct(ClientProxy $client)
    {
        parent::__construct($client);

        if ($client->getApiProxyUrl()) {
            $this->baseUrl = $client->getApiProxyUrl();
        }
    }
}
