<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

/**
 * Class ProxyRequest.
 */
class SimpleProxyRequest implements ProxyRequestInterface
{
    /**
     * @var string
     */
    private $proxyMethod;

    /**
     * @var string
     */
    private $proxyUrl;

    /**
     * @var array
     */
    private $proxyHeaders;

    /**
     * Constructor.
     *
     * @param string $proxyMethod
     * @param string $proxyUrl
     * @param array  $proxyHeaders
     */
    public function __construct($proxyMethod, $proxyUrl, array $proxyHeaders)
    {
        $this->proxyMethod  = $proxyMethod;
        $this->proxyUrl     = $proxyUrl;
        $this->proxyHeaders = $proxyHeaders;
    }

    /**
     * @return string
     */
    public function getProxyMethod()
    {
        return $this->proxyMethod;
    }

    /**
     * @return string
     */
    public function getProxyUrl()
    {
        return $this->proxyUrl;
    }

    /**
     * @return array
     */
    public function getProxyHeaders()
    {
        return $this->proxyHeaders;
    }

}
