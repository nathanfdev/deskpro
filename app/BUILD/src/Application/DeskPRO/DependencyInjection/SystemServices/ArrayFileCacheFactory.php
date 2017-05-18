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
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Doctrine\Common\Cache\VoidCache;
use Orb\Doctrine\Common\Cache\ArrayFileCache;

class ArrayFileCacheFactory
{
    public static function create($cacheName)
    {
        $cacheName = preg_replace('#[^a-zA-Z0-9\-_\.]#', '_', $cacheName);

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        $path      = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId();
        $cacheFile = $path.DIRECTORY_SEPARATOR.$cacheName.'.cache';

        if (!$path || !is_writable($path)) {
            return self::createNull();
        }

        if (is_file($cacheFile) && !is_writable($cacheFile)) {
            return self::createNull();
        }

        $versionId = defined('DP_BUILD_TIME') ? DP_BUILD_TIME : null;
        $cache     = new ArrayFileCache($cacheFile, $versionId);

        if ($cacheName == 'dql') {
            // Filters out queries with 'IN' components that can pollute the cache
            $cache->setFilter(function ($data) {
                if (!is_object($data)) {
                    return false;
                }
                /* @var $data \Doctrine\ORM\Query\ParserResult */
                $s = $data->getSqlExecutor()->getSqlStatements();
                if (is_string($s)) {
                    // Hard-coded IDs
                    if (preg_match('#IN \(\d#', $s)) {
                        return false;
                    // More than 10 segments
                    } elseif (preg_match('#IN \([?, ]{10,}#', $s)) {
                        return false;
                    }
                }

                return true;
            });

            // Makes sure it doesnt get too big
            $cache->setLimit(350);
        }

        if ($cache instanceof ArrayFileCache) {
            $cache->registerShutdownCommit();
        }

        return $cache;
    }

    public static function createNull()
    {
        static $null_cache;

        if (!$null_cache) {
            $null_cache = new VoidCache();
        }

        return $null_cache;
    }
}
