<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings\Loader;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\NewSettings\SettingsLoaderInterface;

/**
 * Gets the returned array from a php file and uses it as settings. Cache's results in given adapter.
 */
class ConfigPhpFileLoader implements SettingsLoaderInterface
{
    const KEY_PREFIX = 'settings.loader.config_php_file.';

    /**
     * @var string the key we use for cache on this loader
     */
    private $cacheKey;

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var string absolute path to the config file that returns a php array
     */
    private $absFilePath;

    /**
     * Constructor.
     *
     * @param string                $absFilePath
     * @param CacheAdapterInterface $cache
     */
    public function __construct($absFilePath, CacheAdapterInterface $cache)
    {
        $this->cacheKey    = static::KEY_PREFIX.$absFilePath;
        $this->cache       = new ConvenientCache($cache);
        $this->absFilePath = $absFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function load($force = false)
    {
        if (!is_readable($this->getAbsFilePath())) {
            throw new \RuntimeException(sprintf('cannot read settings file "%s"', $this->getAbsFilePath()));
        }

        if ($force) {
            $this->cache->delete($this->cacheKey);
        }

        $that = $this;

        return $this->cache->get(
            $this->cacheKey,
            function () use ($that) {
                return require $that->getAbsFilePath();
            }
        );
    }

    /**
     * @return string
     */
    public function getAbsFilePath()
    {
        return $this->absFilePath;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
}
