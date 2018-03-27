<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;

class RequestSigningStrategyOauth1 implements RequestSigningStrategy
{
    /** @var array|string[] */
    private $credentialsMap;

    /**
     * @param array|string[] $credentialsMap
     */
    public function __construct(array $credentialsMap)
    {
        $this->credentialsMap = $credentialsMap;
    }

    /**
     * @param HttpProxyClientBuilder $clientBuilder
     * @throws RequestSigningStrategyException
     */
    public function configureProxyClient(HttpProxyClientBuilder $clientBuilder)
    {
        if (count($this->credentialsMap) === 1) {
            $serializedConnection = reset($this->credentialsMap);

            try {
                $connection = SerializedOauth1Connection::fromJSON($serializedConnection);
            } catch (\Exception $e) {
                throw RequestSigningStrategyException::createUnexpectedCredentials(null, $e);
            }

            $clientBuilder->useOauth1SigningStrategy($connection);
            return;
        }

        $credentialsArray = array_reduce($this->credentialsMap, function ($carry, $credential) {
            $unserialized = json_decode($credential, true);
            if (! is_array($unserialized)) {
                throw RequestSigningStrategyException::createUnexpectedCredentials();
            }

            return $this->array_merge_recursive_distinct($carry, $unserialized);
        }, []);

        try {
            $connection = SerializedOauth1Connection::fromArray($credentialsArray);
        } catch (\Exception $e) {
            throw RequestSigningStrategyException::createUnexpectedCredentials(null, $e);
        }

        $connection = SerializedOauth1Connection::fromArray($credentialsArray);
        $clientBuilder->useOauth1SigningStrategy($connection);

    }

    private function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}
