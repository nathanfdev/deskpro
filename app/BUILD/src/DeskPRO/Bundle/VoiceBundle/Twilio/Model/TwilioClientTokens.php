<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioClientTokens.
 */
class TwilioClientTokens
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $phoneToken;

    /**
     * Constructor.
     *
     * @param string $phoneToken
     */
    public function __construct($phoneToken)
    {
        $this->phoneToken = $phoneToken;
    }
}
