<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\NewSettings\Loader;

use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSettings\SettingsLoaderInterface;

/**
 * Creates and returns an array of k=>V settings from the `settings` mysql table.
 */
class BrandSettingsLoader implements SettingsLoaderInterface
{
    const CACHE_KEY_PREFIX = 'settings.loader.brand.';

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * Constructor.
     *
     * @param Connection            $db
     * @param CacheAdapterInterface $cache
     */
    public function __construct(Connection $db, CacheAdapterInterface $cache)
    {
        $this->cache = new ConvenientCache($cache);
        $this->db    = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function load($force = false, $brand_id = null)
    {
        // NOTE: when you use the main TestSettingsResolver->getBrandSettings() it
        // will first load the global settings and use these brand specific settings on top of that
        // in short: don't use this class by itself
        if (!$brand_id) {
            throw new \InvalidArgumentException('must pass a brand id');
        }

        $cacheKey = self::CACHE_KEY_PREFIX.$brand_id;

        if ($force) {
            $this->cache->delete($cacheKey);
        }

        $conn = $this->db;

        return $this->cache->get(
            $cacheKey,
            function () use ($conn, $brand_id) {
                // start with the settings_brand table
                try {
                    $db_brand_settings = $conn->fetchAllKeyValue(
                        '
                            SELECT name, value
                            FROM settings_brand
                            WHERE brand_id = :brand_id
                        ',
                        ['brand_id' => $brand_id]
                    );
                } catch (\Exception $e) {
                    $db_brand_settings = [];
                }

                /* @var \DpRun\DpEnv $DP_ENV */
                global $DP_ENV;

                $global_settings_key = 'BRAND_'.$brand_id.'_SETTINGS';
                $global_settings = $DP_ENV->getConfig('settings.'.$global_settings_key, []);

                return array_merge($db_brand_settings, $global_settings);
            }
        );
    }
}
