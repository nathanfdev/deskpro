<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Psr\Log\LoggerInterface;

class DbalQueryCacher
{
    /**
     * @var CacheAdapterInterface
     */
    private $cache_adapter;

    /**
     * @var DbalQuerySerializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CacheAdapterInterface $cache_adapter
     * @param DbalQuerySerializer   $serializer
     * @param LoggerInterface       $logger
     */
    public function __construct(CacheAdapterInterface $cache_adapter, DbalQuerySerializer $serializer, LoggerInterface $logger)
    {
        $this->cache_adapter = $cache_adapter;
        $this->serializer    = $serializer;
        $this->logger        = $logger;
    }

    /**
     * @param string $requested_key
     *
     * @return DbalQuery
     */
    public function fetchQuery($requested_key)
    {
        $key        = $this->generateKey($requested_key);
        $serialized = $this->cache_adapter->get($key);

        if ($serialized) {
            $this->logger->debug('DbalQueryCacher: cache hit');
        } else {
            $this->logger->debug('DbalQueryCacher: cache miss');
        }

        $compiled_query = $this->serializer->unserialize($serialized);

        return $compiled_query;
    }

    /**
     * @param string    $requested_key
     * @param DbalQuery $compiled_query
     */
    public function saveQuery($requested_key, DbalQuery $compiled_query)
    {
        $serialized = $this->serializer->serialize($compiled_query);
        $key        = $this->generateKey($requested_key);

        $this->cache_adapter->set($key, $serialized);
        $this->logger->debug('DbalQueryCacher: serialized and saved compiled query');
    }

    /**
     * Prefixes the key from the engine to ensure its unique in the global cache namespace.
     *
     * @param $key
     *
     * @return string
     */
    private function generateKey($key)
    {
        $add_unique = sprintf('dbal.term_engine.query.%s', $key);

        $this->logger->debug(
            'DbalQueryCacher: Prefixing key',
            [
                'requested_key'      => $key,
                'using_prefixed_key' => $add_unique,
            ]
        );

        return $add_unique;
    }
}
