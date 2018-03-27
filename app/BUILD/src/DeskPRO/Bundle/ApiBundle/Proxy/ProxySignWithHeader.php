<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

class ProxySignWithHeader
{
    /** @var string */
    private $strategy;

    /** @var array|string[] */
    private $credentials;

    const NAME = 'X-Proxy-SignWith';

    public function __construct($algorithm, array $credentials)
    {
        $this->strategy   = $algorithm;
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getSignWithStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return array|string[]
     */
    public function getCredentialNames()
    {
        return $this->credentials;
    }
}
