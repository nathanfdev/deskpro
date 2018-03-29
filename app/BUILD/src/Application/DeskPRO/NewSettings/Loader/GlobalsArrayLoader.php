<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings\Loader;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\NewSettings\SettingsLoaderInterface;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;

/**
 * Gets the returned array from the global array.
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

    /**
     * @var AppEnvInterface
     */
    private $env;

    /**
     * Constructor.
     *
     * @param CacheAdapterInterface $cache
     * @param AppEnvInterface       $env
     */
    public function __construct(AppEnvInterface $env, CacheAdapterInterface $cache)
    {
        $this->cacheKey = static::CACHE_KEY;
        $this->cache    = new ConvenientCache($cache);
        $this->env      = $env;
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
                return $this->env->getConfig('settings', []);
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
