<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Annotation\Metadata;

use Metadata\Cache\FileCache;
use Metadata\ClassMetadata;

/**
 * Class MetadataCache.
 */
class MetadataCache extends FileCache
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * @param string $kernel_cache_dir
     * @param string $cache_dir
     */
    public function __construct($kernel_cache_dir, $cache_dir)
    {
        $this->cache_dir = $cache_dir;
        $this->dir       = rtrim($this->getCacheDir($kernel_cache_dir), '\\/');
    }

    /**
     * @param string $kernel_cache_dir
     *
     * @return string
     */
    protected function getCacheDir($kernel_cache_dir)
    {
        $cache_dir = $kernel_cache_dir.DIRECTORY_SEPARATOR.$this->cache_dir;
        if (!file_exists($cache_dir)) {
            if (!$rs = @mkdir($cache_dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $cache_dir));
            }
        }

        return $cache_dir;
    }

    /**
     * @param $data_name
     *
     * @return string
     */
    protected function getFilePath($data_name)
    {
        return $this->dir.'/'.strtr($data_name, '\\', '-').'.cache.php';
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadataFromCache(\ReflectionClass $class)
    {
        $path = $this->getFilePath($class->name);
        if (!file_exists($path)) {
            return;
        }

        return include $path;
    }

    /**
     * {@inheritdoc}
     */
    public function putClassMetadataInCache(ClassMetadata $metadata)
    {
        $path                      = $this->getFilePath($metadata->name);
        $metadata->fileResources[] = $path;
        $tmpFile                   = tempnam($this->dir, 'metadata-cache');
        file_put_contents($tmpFile, '<?php return unserialize('.var_export(serialize($metadata), true).');');
        chmod($tmpFile, 0666 & ~umask());

        $this->renameFile($tmpFile, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function evictClassMetadataFromCache(\ReflectionClass $class)
    {
        $path = $this->getFilePath($class->name);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Renames a file with fallback for windows
     * !!! People who creating private libraries should BURN !!!
     *
     * @param string $source
     * @param string $target
     */
    protected function renameFile($source, $target)
    {
        if (false === @rename($source, $target)) {
            throw new \RuntimeException(sprintf('Could not write new cache file to %s.', $target));
        }
    }
}
