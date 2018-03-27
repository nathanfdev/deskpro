<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Model;

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
    private $workerToken;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $phoneToken;

    /**
     * Constructor.
     *
     * @param string $workerToken
     * @param string $phoneToken
     */
    public function __construct($workerToken, $phoneToken)
    {
        $this->workerToken = $workerToken;
        $this->phoneToken  = $phoneToken;
    }
}
