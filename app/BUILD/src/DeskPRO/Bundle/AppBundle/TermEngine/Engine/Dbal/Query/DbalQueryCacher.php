<?php

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
