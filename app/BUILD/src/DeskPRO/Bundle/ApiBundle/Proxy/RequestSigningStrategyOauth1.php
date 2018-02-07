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
