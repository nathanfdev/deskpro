<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Cache\Adapter;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Entity\Cache;
use Doctrine\ORM\EntityManager;

/**
 * Cache using the
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
        $this->em = $em;
        $this->expiration_interval_in_seconds = $expiration_interval_in_seconds;
    }

    /**
     * sets the key => val in the ORM
     *
     * @param $key
     * @param $val
     * @return null
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
        $this->em->flush($cache);

        return null;
    }

    /**
     * Fetch the data of the cache entry for key, but WILL NOT return it if it is expired.
     *
     * @param string $key
     * @return null|string
     */
    public function get($key)
    {
        /** @var \Application\DeskPRO\Entity\Cache $cache */
        $cache = $this->getCacheRepo()->find($key);

        if ($cache && !$cache->isExpired()) {

            return $cache->getData();

        }

        return null;
    }

    /**
     * Delete the entity (flushes)
     *
     * @param string $key
     * @return null
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            $cache = $this->getCacheRepo()->find($key);
            $this->em->remove($cache);
            $this->em->flush($cache);
        }

        return null;
    }

    /**
     * True if a cache entry exists for the key, and that is is NOT expired
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        /** @var \Application\DeskPRO\Entity\Cache $cache */
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
