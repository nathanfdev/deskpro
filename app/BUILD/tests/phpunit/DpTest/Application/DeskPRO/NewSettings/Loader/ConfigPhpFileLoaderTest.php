<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\NewSettings\Loader;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\NewSettings\Loader\ConfigPhpFileLoader;
use DpTest\DeskProTestCase;
use Mockery\Mock;

class ConfigPhpFileLoaderTest extends DeskProTestCase
{
    public function testInvalidCaseExceptions()
    {
        $loader = new ConfigPhpFileLoader('path/wont/be/found', $cache = new SimpleArrayCache());

        $this->setExpectedException('RuntimeException');

        $loader->load();
    }

    public function testLoadingWorksAndStoresInCache()
    {
        $config_file_path = __DIR__.'/fixtures/configs_file.php';

        $expectedSettings = [
            'key'       => 'val',
            'extra_key' => 'extra_val',
        ];

        $cache_key = 'settings.loader.config_php_file.'.$config_file_path;

        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $mockCache->shouldReceive('has')->with($cache_key)->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with($cache_key, $expectedSettings)->once();
        $mockCache->shouldReceive('delete')->never();

        $loader = new ConfigPhpFileLoader($config_file_path, $mockCache);

        // asserting that the correct array is received from the config file and that cache was set properly
        $this->assertSame(
            $expectedSettings,
            $loader->load()
        );
    }

    public function testLoadingUsesCacheIfExists()
    {
        $config_file_path = __DIR__.'/fixtures/configs_file.php';

        $expectedSettings = [
            'key'       => 'val',
            'extra_key' => 'extra_val',
        ];

        $cache_key = 'settings.loader.config_php_file.'.$config_file_path;

        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $mockCache->shouldReceive('has')->with($cache_key)->andReturn(true)->once();
        $mockCache->shouldReceive('get')->with($cache_key)->andReturn($expectedSettings)->once();
        $mockCache->shouldReceive('delete')->never();

        $loader = new ConfigPhpFileLoader($config_file_path, $mockCache);

        // asserting that the correct array is received from the cache
        $this->assertSame(
            $expectedSettings,
            $loader->load()
        );
    }

    public function testForceReload()
    {
        $config_file_path = __DIR__.'/fixtures/configs_file.php';

        $expectedSettings = [
            'key'       => 'val',
            'extra_key' => 'extra_val',
        ];

        $cache_key = 'settings.loader.config_php_file.'.$config_file_path;

        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $mockCache->shouldReceive('delete')->with($cache_key)->once();
        $mockCache->shouldReceive('has')->with($cache_key)->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with($cache_key, $expectedSettings)->once();

        $loader = new ConfigPhpFileLoader($config_file_path, $mockCache);

        // asserting that we delete the cache key and regenrate cache
        $this->assertSame(
            $expectedSettings,
            $loader->load(true)
        );
    }
}
