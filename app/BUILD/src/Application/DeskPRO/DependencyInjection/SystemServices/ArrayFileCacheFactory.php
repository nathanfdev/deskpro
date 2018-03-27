<?php

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
