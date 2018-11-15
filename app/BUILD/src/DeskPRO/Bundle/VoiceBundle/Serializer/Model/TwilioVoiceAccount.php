<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioVoiceAccount.
 */
class TwilioVoiceAccount extends AbstractVoiceAccount
{
    /**
     * @JMS\Type("deferred<DeskPRO\Bundle\VoiceBundle\Serializer\Model\TwilioClientCredentials>")
     *
     * @var string
     */
    protected $clientCredentials;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type = 'twilio';

    /**
     * @param CallbackDeferredProperty $clientCredentials
     */
    public function setClientCredentials($clientCredentials)
    {
        $this->clientCredentials = $clientCredentials;
    }
}
