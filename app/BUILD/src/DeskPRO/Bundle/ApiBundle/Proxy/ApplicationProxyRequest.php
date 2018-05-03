<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

/**
 * Class ProxyRequest.
 */
class ApplicationProxyRequest implements ProxyRequestInterface
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
     * @var string[]
     */
    private $whiteList;

    /**
     * @var RequestSigningStrategy
     */
    private $signingStrategy;

    /**
     * Constructor.
     *
     * @param string                 $proxyMethod
     * @param string                 $proxyUrl
     * @param array                  $proxyHeaders
     * @param array                  $whiteList
     * @param RequestSigningStrategy $signingStrategy
     */
    public function __construct($proxyMethod, $proxyUrl, array $proxyHeaders, array $whiteList, RequestSigningStrategy $signingStrategy = null)
    {
        $this->proxyMethod     = $proxyMethod;
        $this->proxyUrl        = $proxyUrl;
        $this->proxyHeaders    = $proxyHeaders;
        $this->whiteList       = $whiteList;
        $this->signingStrategy = $signingStrategy;
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

    /**
     * @return array|null
     */
    public function getProxyHeader($name)
    {
        if (!is_string($name)) {
            return null;
        }

        $headerKey = strtolower($name);
        return array_key_exists($headerKey, $this->proxyHeaders) ? $this->proxyHeaders[$headerKey] : null;
    }

    /**
     * @return \string[]
     */
    public function getWhiteList()
    {
        return $this->whiteList;
    }

    /**
     * @return RequestSigningStrategy|null
     */
    public function getSigningStrategy()
    {
        return $this->signingStrategy;
    }
}
