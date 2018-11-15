<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PlivoVoiceAccount.
 */
class PlivoVoiceAccount extends AbstractVoiceAccount
{
    /**
     * @JMS\Type("deferred<DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoClientCredentials>")
     *
     * @var string
     */
    protected $clientCredentials;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type = 'plivo';

    /**
     * @param CallbackDeferredProperty $clientCredentials
     */
    public function setClientCredentials($clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }
}
