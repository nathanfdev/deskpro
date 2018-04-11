<?php

namespace DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;

class RequestSigningStrategyOauth1 implements RequestSigningStrategy
{
    /**
     * @param array $credentialsMap
     * @return RequestSigningStrategyOauth1
     * @throws RequestSigningStrategyException
     */
    public static function fromRequestVariables( array $credentialsMap)
    {
        $credentialsArray = array_reduce($credentialsMap, function ($carry, $credential) {
            $nextCredential = null;
            if (is_array($credential)){
                $nextCredential = $credential;
            } else if ($credential instanceof \stdClass) {
                $nextCredential = json_decode(json_encode($credential), true);
            } else {
                $nextCredential = json_decode($credential, true);
            }

            if (! is_array($nextCredential)) {
                throw RequestSigningStrategyException::createUnexpectedCredentials();
            }

            return RequestSigningStrategyOauth1::array_merge_recursive_distinct($carry, $nextCredential);
        }, []);

        try {
            $connection = SerializedOauth1Connection::fromArray($credentialsArray);
        } catch (\Exception $e) {
            throw RequestSigningStrategyException::createUnexpectedCredentials(null, $e);
        }

        return new RequestSigningStrategyOauth1($connection);
    }

    private static function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = RequestSigningStrategyOauth1::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /** @var SerializedOauth1Connection */
    private $connection;

    /**
     * @param SerializedOauth1Connection $connection
     */
    public function __construct(SerializedOauth1Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param HttpProxyClientBuilder $clientBuilder
     * @throws RequestSigningStrategyException
     */
    public function configureProxyClient(HttpProxyClientBuilder $clientBuilder)
    {
        $clientBuilder->useOauth1SigningStrategy($this->connection);
    }


}
