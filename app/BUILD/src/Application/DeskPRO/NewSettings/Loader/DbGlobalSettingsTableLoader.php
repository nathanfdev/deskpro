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
class DbGlobalSettingsTableLoader implements SettingsLoaderInterface
{
    const CACHE_KEY = 'settings.loader.db_global_settings_table';

    /**
     * @var string the key we use for cache on this loader
     */
    private $cacheKey;

    /**
     * @var \Application\DeskPRO\Cache\ConvenientCache
     */
    private $cache;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    private $whitelistedKeys = [
        'notification.settings.default_strategy',
        'notification.settings.strategies',
    ];

    /**
     * Constructor.
     *
     * @param Connection            $db
     * @param CacheAdapterInterface $cache
     */
    public function __construct(Connection $db, CacheAdapterInterface $cache)
    {
        $this->cacheKey = static::CACHE_KEY;
        $this->cache    = new ConvenientCache($cache);
        $this->db       = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function load($force = false)
    {
        if ($force) {
            $this->cache->delete($this->cacheKey);
        }

        $conn = $this->db;

        return $this->cache->get(
            $this->cacheKey,
            function () use ($conn) {
                try {
                    $config = $conn->fetchAllKeyValue(
                        '
                            SELECT name, value
                            FROM settings
                        '
                    );
                    // we're not about iterate over all values, we gonna pick only whitelisted keys
                    // also we can't determine if value is serialized string or just scalar string,
                    // so the best way to check it's serialized string - unserialize it with shut up
                    // and then check if it's not serialized "false" boolean
                    foreach ($this->whitelistedKeys as $key) {
                        if (array_key_exists($key, $config)) {
                            $value = $config[$key];
                            $newValue = @unserialize($value);
                            if ($value === 'b:0;' || false !== $newValue) {
                                $config[$key] = $newValue;
                            }
                        }
                    }

                    return $config;
                } catch (\Exception $e) {
                    // during install and such, we expect this to happen when no "settings" table exists
                    return [];
                }
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
