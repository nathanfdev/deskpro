<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
            array(
                'requested_key'      => $key,
                'using_prefixed_key' => $add_unique,
            )
        );

        return $add_unique;
    }
}
