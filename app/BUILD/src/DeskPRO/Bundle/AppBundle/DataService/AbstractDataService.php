<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use Doctrine\ORM\EntityManager;

/**
 * Provides a simple interface to cache various data requests so that data requests through the DataServices' apis are
 * only computed ONCE in any given http request.
 *
 * Multiple calls to the same method with the same params are retrieved from memory (an array).
 */
class AbstractDataService
{
    /**
     * @var ArbitraryHasher|null
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * NOTE: $params MUST be unique as it is hashed into a key. By convention pass the method name of the
     * method that is using this method as the first parameter. Otherwise you may have conflicts between
     * different methods using the same params that are actually caching different things.
     *
     * Stores the result of $callable in an array in case this is fetched frequently in this request.
     *
     * @param mixed $params         the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable       doesn't need to be a callable, can be any default value, but usually is a callable
     * @param mixed $callableParams list of arguments for callable
     *
     * @return mixed|null
     */
    protected function generateAndCache($params, $callable, array $callableParams = [])
    {
        return $this->getCache()->get($this->generateHash($params), $callable, $callableParams);
    }

    /**
     * @return ConvenientCache
     */
    protected function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ConvenientCache(new SimpleArrayCache());
        }

        return $this->cache;
    }

    /**
     * @param mixed $input
     *
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitraryHasher();
        }

        return $this->hash_generator->generateHash($input);
    }
}
