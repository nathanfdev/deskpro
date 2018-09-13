<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class PlivoEndpoint.
 */
class PlivoEndpoint
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $username;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $password;

    /**
     * Constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}
