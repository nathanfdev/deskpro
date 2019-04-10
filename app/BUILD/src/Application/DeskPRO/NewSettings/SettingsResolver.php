<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Brand;

/**
 * Class SettingsResolver.
 */
class SettingsResolver
{
    const CACHE_KEY_GLOBAL       = 'settings.bag.global';
    const CACHE_KEY_DEFAULT      = 'settings.bag.default';
    const CACHE_KEY_BRAND_PREFIX = 'settings.bag.brand';

    /**
     * @var SettingsLoaderInterface[]
     */
    private $loaders;

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var array
     */
    private $virtual_settings;

    /**
     * Loader responsible for brand specific settings.
     *
     * @var SettingsLoaderInterface
     */
    private $brand_settings_loader;

    /**
     * Constructor.
     *
     * @param array                   $loaders
     * @param CacheAdapterInterface   $cache
     * @param SettingsLoaderInterface $brand_settings_loader
     */
    public function __construct(array $loaders, CacheAdapterInterface $cache, SettingsLoaderInterface $brand_settings_loader)
    {
        $this->loaders               = $loaders;
        $this->cache                 = new ConvenientCache($cache);
        $this->virtual_settings      = [];
        $this->brand_settings_loader = $brand_settings_loader;
    }

    /**
     * @return SettingsLoaderInterface[]|array
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * @param bool $force
     *
     * @return SettingsBag
     */
    public function getGlobalSettings($force = false)
    {
        if ($force) {
            $this->cache->delete(static::CACHE_KEY_GLOBAL);
        }

        return $this->cache->get(
            static::CACHE_KEY_GLOBAL,
            function () use ($force) {
                $settings = [];
                foreach ($this->getLoaders() as $loader) {
                    $settings = array_merge($settings, $loader->load($force));
                }
                foreach ($this->virtual_settings as $key => $callable) {
                    $settings[$key] = call_user_func($callable, $settings);
                }

                return new SettingsBag($settings);
            }
        );
    }

    /**
     * Gets the settings bag for the given brand ID.
     *
     * @param Brand|int $brand_id
     * @param bool      $force
     *
     * @return SettingsBag
     */
    public function getBrandSettings($brand_id, $force = false)
    {
        if ($brand_id instanceof Brand) {
            $brand_id = $brand_id->id;
        }

        if (!$brand_id > 0) {
            $brand_id = 0;
        }

        $cacheKey = static::CACHE_KEY_BRAND_PREFIX.'.brand'.$brand_id;

        if ($force) {
            $this->cache->delete($cacheKey);
        }

        $brand_settings_resolver = $this->brand_settings_loader;
        $global_settings         = $this->getGlobalSettings($force);

        return $this->cache->get(
            $cacheKey,
            function () use ($brand_settings_resolver, $global_settings, $brand_id, $force) {
                $global_settings_array = $global_settings->toArray();

                $brand_settings = $brand_id ? $brand_settings_resolver->load($force, $brand_id) : [];
                $brand_settings_array = array_merge($global_settings_array, $brand_settings);

                return new SettingsBag($brand_settings_array);
            }
        );
    }

    /**
     * @param bool $force
     *
     * @return SettingsBag
     */
    public function getDefaultSettings($force = false)
    {
        if ($force) {
            $this->cache->delete(static::CACHE_KEY_DEFAULT);
        }

        $that             = $this;
        $virtual_settings = $this->virtual_settings;

        return $this->cache->get(
            static::CACHE_KEY_DEFAULT,
            function () use ($that, $force, $virtual_settings) {
                $default_settings = [];

                $loaders = $that->getLoaders();
                if (count($loaders) > 0) {
                    $default_loader = $loaders[0];
                    $default_settings = $default_loader->load($force);
                }

                foreach ($virtual_settings as $key => $callable) {
                    $default_settings[$key] = call_user_func($callable, $default_settings);
                }

                return new SettingsBag($default_settings);
            }
        );
    }

    /**
     * @param $setting
     * @param $callable
     */
    public function setVirtual($setting, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('addVirtual must pass in a callable');
        }

        $this->virtual_settings[$setting] = $callable;
    }
}
