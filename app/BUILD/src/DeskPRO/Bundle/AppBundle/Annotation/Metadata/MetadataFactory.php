<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\Metadata;

use Metadata\Cache\CacheInterface;
use Metadata\ClassMetadata;
use Metadata\Driver\DriverInterface;
use Metadata\MetadataFactoryInterface;

/**
 * Class MetadataFactory.
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var array
     */
    public $loaded_metadata = [];

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver, CacheInterface $cache, $debug = false)
    {
        $this->debug  = $debug;
        $this->driver = $driver;
        $this->cache  = $cache;
    }

    /**
     * @param string $class_name
     * @param bool   $force_rewrite
     *
     * @return ClassMetadata
     */
    public function getMetadataForClass($class_name, $force_rewrite = false)
    {
        if (isset($this->loaded_metadata[$class_name]) && !$force_rewrite) {
            return $this->loaded_metadata[$class_name];
        }
        $reflection = new \ReflectionClass($class_name);
        try {
            if (null !== $classMetadata = $this->cache->loadClassMetadataFromCache($reflection)) {
                if ($this->debug || $force_rewrite) {
                    $this->cache->evictClassMetadataFromCache($reflection);
                } else {
                    $this->loaded_metadata[$class_name] = $classMetadata;

                    return $this->loaded_metadata[$class_name];
                }
            }
        } catch (\ReflectionException $e) {
            // something was changed, so we gonna evict metadata from cache
            $this->cache->evictClassMetadataFromCache($reflection);
        }

        if (null !== $classMetadata = $this->driver->loadMetadataForClass($reflection)) {
            $this->loaded_metadata[$class_name] = $classMetadata;
            $this->cache->putClassMetadataInCache($classMetadata);

            return $this->loaded_metadata[$class_name];
        }

        return;
    }
}
