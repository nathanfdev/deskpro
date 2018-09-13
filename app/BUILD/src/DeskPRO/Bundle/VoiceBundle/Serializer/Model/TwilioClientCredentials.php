<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioClientCredentials.
 */
class TwilioClientCredentials
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $phoneToken;

    /**
     * @param string $phoneToken
     */
    public function setPhoneToken($phoneToken)
    {
        $this->phoneToken = $phoneToken;
    }
}
