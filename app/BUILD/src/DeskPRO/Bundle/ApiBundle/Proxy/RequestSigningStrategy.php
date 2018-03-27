<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

interface RequestSigningStrategy
{
    const STRATEGY_OAUTH1 = 'oauth1';

    function configureProxyClient(HttpProxyClientBuilder $clientBuilder);
}
