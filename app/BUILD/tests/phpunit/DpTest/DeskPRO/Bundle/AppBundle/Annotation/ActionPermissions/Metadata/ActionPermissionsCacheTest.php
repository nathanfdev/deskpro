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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use DpTest\ApiTestCase;
use Metadata\ClassMetadata;

class ActionPermissionsCacheTest extends ApiTestCase
{
    /** @var  ActionPermissionsCache */
    protected static $cache;

    public static function setUpBeforeClass()
    {
        /** \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        self::$cache = new ActionPermissionsCache($DP_ENV->getAppBaseKernelCacheDir());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnableCreateCacheDir()
    {
        new ActionPermissionsCache('/action_permissions');
    }

    public function testCreateCache()
    {
        self::$cache->putClassMetadataInCache(new ClassMetadata(ActionPermissionsCache::class));
    }

    public function testReadCache()
    {
        $class_metadata = self::$cache->loadClassMetadataFromCache(new \ReflectionClass(ActionPermissionsCache::class));
        $this->assertTrue(is_object($class_metadata));
        $this->assertTrue($class_metadata instanceof ClassMetadata);
    }

    public function testReadUnexistingCache()
    {
        $class_metadata = self::$cache->loadClassMetadataFromCache(new \ReflectionClass(self::class));
        $this->assertFalse(is_object($class_metadata));
        $this->assertFalse($class_metadata instanceof ClassMetadata);
    }

    public function testEraseCache()
    {
        self::$cache->evictClassMetadataFromCache(new \ReflectionClass(ActionPermissionsCache::class));
    }

    public static function tearDownAfterClass()
    {
        /** \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        rmdir($DP_ENV->getAppBaseKernelCacheDir().'/api_permissions');
    }
}
