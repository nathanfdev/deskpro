<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\Annotation\Metadata;

use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataCache;
use DpTest\ApiTestCase;
use Metadata\ClassMetadata;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MetadataCacheTest.
 */
class MetadataCacheTest extends ApiTestCase
{
    /** @var MetadataCache */
    protected static $cache;

    public static function setUpBeforeClass()
    {
        self::$cache = new MetadataCache(self::getEnvironmentCacheDir(), 'metadata_cache');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnableCreateCacheDir()
    {
        new MetadataCache('/', 'metadata_cache');
    }

    /**
     * test cache could be created.
     */
    public function testCreateCache()
    {
        self::$cache->putClassMetadataInCache(new ClassMetadata(MetadataCache::class));
    }

    /**
     * test cache could be read.
     */
    public function testReadCache()
    {
        $class_metadata = self::$cache->loadClassMetadataFromCache(new \ReflectionClass(MetadataCache::class));
        $this->assertTrue(is_object($class_metadata));
        $this->assertTrue($class_metadata instanceof ClassMetadata);
    }

    /**
     * test reading unexistent cache.
     */
    public function testReadUnexistentCache()
    {
        $class_metadata = self::$cache->loadClassMetadataFromCache(new \ReflectionClass(self::class));
        $this->assertFalse(is_object($class_metadata));
        $this->assertFalse($class_metadata instanceof ClassMetadata);
    }

    /**
     * test cache erasing.
     */
    public function testEraseCache()
    {
        self::$cache->evictClassMetadataFromCache(new \ReflectionClass(MetadataCache::class));
    }

    public static function tearDownAfterClass()
    {
        $fs = new Filesystem();
        $fs->remove(self::getEnvironmentCacheDir().'/metadata_cache');
    }

    /**
     * @return string
     */
    protected static function getEnvironmentCacheDir()
    {
        /* \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId();
    }
}
