<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PlivoClientCredentials.
 */
class PlivoClientCredentials
{
    /**
     * @JMS\Type("DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoEndpoint")
     *
     * @var string
     */
    protected $endpoint;

    /**
     * @param PlivoEndpoint $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
