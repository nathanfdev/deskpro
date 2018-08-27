<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Pricing;

/**
 * Class PricingProxy.
 */
class PricingProxy extends Pricing
{
    /**
     * {@inheritdoc}
     *
     * @param ClientProxy $client
     */
    public function __construct(ClientProxy $client)
    {
        parent::__construct($client);

        if ($client->getProxyPricingUrl()) {
            $this->baseUrl = $client->getProxyPricingUrl();
        }
    }
}
