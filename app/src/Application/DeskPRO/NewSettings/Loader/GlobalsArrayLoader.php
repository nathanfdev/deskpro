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

namespace Application\DeskPRO\NewSettings\Loader;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\NewSettings\SettingsLoaderInterface;

/**
 * Gets the returned array from the global array
 */
class GlobalsArrayLoader implements SettingsLoaderInterface
{
    const CACHE_KEY = 'settings.loader.global_array';

    /**
     * @var string the key we use for cache on this loader
     */
    private $cacheKey;

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;


    public function __construct(CacheAdapterInterface $cache)
    {
        $this->cacheKey = static::CACHE_KEY;
        $this->cache = new ConvenientCache($cache);
    }

    /**
     * {@inheritdoc}
     */
    public function load($force = false)
    {
        if ($force) {
            $this->cache->delete($this->cacheKey);
        }

        return $this->cache->get(
            $this->cacheKey,
            function () {
                if (isset($GLOBALS['DP_CONFIG']['SETTINGS']) && is_array($GLOBALS['DP_CONFIG']['SETTINGS'])) {
                    return $GLOBALS['DP_CONFIG']['SETTINGS'];
                }

                return array();
            }
        );
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
}
