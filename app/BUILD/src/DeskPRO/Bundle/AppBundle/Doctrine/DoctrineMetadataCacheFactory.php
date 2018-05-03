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
            return new FilesystemCache($appEnv->getAppBaseKernelCacheDir().'/'.$appEnv->getEnvId().'/'.$subDir, $extension);
        }
    }
}
