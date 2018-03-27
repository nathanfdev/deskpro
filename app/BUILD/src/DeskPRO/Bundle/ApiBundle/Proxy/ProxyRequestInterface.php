<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

/**
 * Class ProxyRequest.
 */
interface ProxyRequestInterface
{
    /**
     * @return string
     */
    public function getProxyMethod();

    /**
     * @return string
     */
    public function getProxyUrl();

    /**
     * @return array
     */
    public function getProxyHeaders();
}
