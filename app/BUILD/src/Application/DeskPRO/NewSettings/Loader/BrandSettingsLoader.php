<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
