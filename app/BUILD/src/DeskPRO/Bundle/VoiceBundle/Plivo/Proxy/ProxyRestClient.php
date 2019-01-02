<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Proxy;

use Plivo\RestClient;

/**
 * Class ProxyRestClient.
 */
class ProxyRestClient extends RestClient
{
    /**
     * Constructor.
     *
     * @param string $authId
     * @param string $authToken
     * @param string $proxyHost
     * @param string $proxyUsername
     * @param string $proxyPassword
     */
    public function __construct($authId, $authToken, $proxyHost, $proxyUsername, $proxyPassword)
    {
        $this->client = new ProxyBaseClient($authId, $authToken, $proxyHost, $proxyUsername, $proxyPassword);
    }
}
