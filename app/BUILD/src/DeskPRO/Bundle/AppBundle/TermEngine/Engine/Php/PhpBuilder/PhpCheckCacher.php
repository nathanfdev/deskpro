<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Psr\Log\LoggerInterface;

class PhpCheckCacher
{
    /**
     * @var CacheAdapterInterface
     */
    private $cache_adapter;

    /**
     * @var PhpCheckSerializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CacheAdapterInterface $cache_adapter, PhpCheckSerializer $serializer, LoggerInterface $logger)
    {
        $this->cache_adapter = $cache_adapter;
        $this->serializer    = $serializer;
        $this->logger        = $logger;
    }

    public function fetchCheck($requested_key)
    {
        $key = $this->generateKey($requested_key);

        $serialized = $this->cache_adapter->get($key);

        if ($serialized) {
            $this->logger->debug('PhpCheckCacher: cache hit');
        } else {
            $this->logger->debug('PhpCheckCacher: cache miss');
        }

        $compiled_query = $this->serializer->unserialize($serialized);

        return $compiled_query;
    }

    public function saveCheck($requested_key, PhpCheck $php_check)
    {
        $serialized = $this->serializer->serialize($php_check);

        $key = $this->generateKey($requested_key);

        $this->cache_adapter->set($key, $serialized);

        $this->logger->debug('PhpCheckCacher: serialized and saved compiled php check');
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
        $add_unique = sprintf('php.term_engine.php_check.%s', $key);

        $this->logger->debug(
            'PhpCheckCacher: Prefixing key',
            [
                'requested_key'      => $key,
                'using_prefixed_key' => $add_unique,
            ]
        );

        return $add_unique;
    }
}
