<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Crypto;

use JMS\Serializer\Annotation as JMS;

class KeyPair
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $publicKey;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $privateKey;

    /**
     * KeyPair constructor.
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct( $publicKey, $privateKey )
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }
}


