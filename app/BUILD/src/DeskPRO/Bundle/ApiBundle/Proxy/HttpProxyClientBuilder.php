<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;
use GuzzleHttp\HandlerStack;

class HttpProxyClientBuilder
{
    private $config = [];

    private function useOauth1SigningStrategy($serializedConnection)
    {
        $connection = SerializedOauth1Connection::fromJSON($serializedConnection);
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

    public function setSigningStrategy(RequestSigningStrategy $strategy)
    {
        $algorithm = $strategy->getAlgorithm();
        if (strtolower($algorithm) === 'oauth1') {
            $this->useOauth1SigningStrategy($strategy->getCredentials());
        }

        return $this;
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
