<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;
use GuzzleHttp\HandlerStack;

class HttpProxyClientBuilder
{
    private $config = [];

    public function useOauth1SigningStrategy( SerializedOauth1Connection $connection)
    {
        $config     = [
            'token'           => $connection->getToken(),
            'token_secret'    => $connection->getTokenSecret(),
            'consumer_secret' => $connection->getClientSecret(),
            'consumer_key'    => $connection->getClientId(),
        ];

        $privateKey = $connection->getRSAPrivateKey();
        if ($privateKey) {
            $config['signature_method'] = HttpProxyClientSubscriber::SIGNATURE_METHOD_RSA;
            $config['private_key']      = $connection->getRSAPrivateKey();
        }

        $this->config['handler'] = function () use ($config) {
            $stack = HandlerStack::create();
            $stack->push(new HttpProxyClientSubscriber($config));

            return $stack;
        };

        $this->config['auth'] = 'oauth';
    }

    public function setTimeout($timeout)
    {
        $this->config['timeout'] = $timeout;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function build()
    {
        $config = [];
        foreach ($this->config as $key => $value) {
            if ($value instanceof \Closure) {
                $config[$key] = $value();
            } else {
                $config[$key] = $value;
            }
        }

        return new HttpClient($config);
    }
}
