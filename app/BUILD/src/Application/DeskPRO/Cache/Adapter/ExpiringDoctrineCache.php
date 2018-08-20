<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Cache\Adapter;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Entity\Cache;
use Doctrine\ORM\EntityManager;

/**
 * Cache using the.
 */
class ExpiringDoctrineCache implements CacheAdapterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int
     */
    private $expiration_interval_in_seconds;

    public function __construct(EntityManager $em, $expiration_interval_in_seconds)
    {
        $this->em                             = $em;
        $this->expiration_interval_in_seconds = $expiration_interval_in_seconds;
    }

    /**
     * sets the key => val in the ORM.
     *
     * @param $key
     * @param $val
     */
    public function set($key, $val)
    {
        // we re-use the same "object" in the ORM, but we update the details
        // we update instead of deleting because the DB may need that cache entry while we gen the new one here
        if (!$cache = $this->getCacheRepo()->find($key)) {
            $cache = new Cache();
        }

        $this->setupNewCacheEntry($cache, $key, $val);

        $this->em->persist($cache);
        $this->em->flush();

        return;
    }

    /**
     * Fetch the data of the cache entry for key, but WILL NOT return it if it is expired.
     *
     * @param string $key
     *
     * @return null|string
     */
    public function get($key)
    {
        /** @var \Application\DeskPRO\Entity\Cache $cache */
        $cache = $this->getCacheRepo()->find($key);

        if ($cache && !$cache->isExpired()) {
            return $cache->getData();
        }

        return;
    }

    /**
     * Delete the entity (flushes).
     *
     * @param string $key
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            $cache = $this->getCacheRepo()->find($key);
            $this->em->remove($cache);
            $this->em->flush();
        }

        return;
    }

    /**
     * True if a cache entry exists for the key, and that is is NOT expired.
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        /* @var \Application\DeskPRO\Entity\Cache $cache */
        if (!$cache = $this->getCacheRepo()->find($key)) {
            return false;
        }

        return !$cache->isExpired();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Cache
     */
    protected function getCacheRepo()
    {
        return $this->em->getRepository('DeskPRO:Cache');
    }

    /**
     * @param $cache Cache
     * @param $key string
     * @param $val mixed
     */
    private function setupNewCacheEntry(Cache $cache, $key, $val)
    {
        $expires = new \DateTime(sprintf('now + %s seconds', $this->expiration_interval_in_seconds));
        $cache->setExpiresAt($expires);
        $cache->setId($key);
        $cache->setData($val);
    }
}
