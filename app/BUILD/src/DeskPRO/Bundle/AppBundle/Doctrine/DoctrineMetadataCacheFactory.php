<?php

namespace DeskPRO\Bundle\AppBundle\Doctrine;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Component\Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

/**
 * Class DoctrineMetadataCacheFactory.
 */
class DoctrineMetadataCacheFactory
{
    /**
     * Constructor.
     *
     * @param AppEnvInterface $appEnv
     * @param string          $subDir
     * @param string          $extension
     *
     * @return CacheProvider
     */
    public static function create(AppEnvInterface $appEnv, $subDir = 'doctrine-metadata', $extension = FilesystemCache::EXTENSION)
    {
        if ($appEnv->getEnvId() === 'dev') {
            return new ArrayCache();
        } else {
            // fixme: can't warm up the cache using the new stream wrapper -- quick and dirty way of doing this for now
            if (php_sapi_name() !== 'cli' && in_array('dpfsproxy', stream_get_wrappers()) && defined('DPC_IS_READ_ONLY_FS')) {
                return new FilesystemCache('dpfsproxy://kernel_cache'.$appEnv->getAppBaseKernelCacheDir().'/'.$appEnv->getEnvId().'/'.$subDir, $extension);
            }

            return new FilesystemCache($appEnv->getAppBaseKernelCacheDir().'/'.$appEnv->getEnvId().'/'.$subDir, $extension);
        }
    }
}
