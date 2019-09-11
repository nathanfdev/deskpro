<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy;

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

    /**
     * {@inheritdoc}
     */
    protected function getV2010()
    {
        if (!$this->_v2010) {
            $this->_v2010 = new V2010Proxy($this);
        }

        return $this->_v2010;
    }
}
