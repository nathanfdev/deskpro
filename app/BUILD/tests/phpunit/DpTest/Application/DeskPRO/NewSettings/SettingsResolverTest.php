<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\NewSettings;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DpTest\DeskProTestCase;

class SettingsResolverTest extends DeskProTestCase
{
    public function testConstructedWithLoaders()
    {
        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

        $loaders = [$mock1, $mock2];

        $resolver = new SettingsResolver(
            $loaders, \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface'),
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $this->assertSame($loaders, $resolver->getLoaders(), 'stored with the same order');
    }

    public function testGlobalSettingsMergesLoadersAndCachesProperly()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

        $resolver = new SettingsResolver(
            [$mock1, $mock2], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $mock1->shouldReceive('load')->andReturn(
            $settings1 = [
                'core.key1' => 'eighteen',
                'core.key2' => 'sixteen',
                'core.key3' => 'number4',
            ]
        );

        $mock2->shouldReceive('load')->andReturn(
            $settings2 = [
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        // reflects the order of the loader return values
        $expectedResolvedSettingsBag = new SettingsBag(
            [
                'core.key1' => 'eighteen',
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        $mockCache->shouldReceive('delete')->with('settings.bag.global')->never();
        $mockCache->shouldReceive('has')->with('settings.bag.global')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with(
            'settings.bag.global',
            \Mockery::type('Application\DeskPRO\NewSettings\SettingsBag')
        )->once();

        $this->assertEquals($expectedResolvedSettingsBag, $resolver->getGlobalSettings());
    }

    public function testGlobalSettingsForceReload()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

        $resolver = new SettingsResolver(
            [$mock1, $mock2], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $mock1->shouldReceive('load')->with(true)->andReturn(
            $settings1 = [
                'core.key1' => 'eighteen',
                'core.key2' => 'sixteen',
                'core.key3' => 'number4',
            ]
        );

        $mock2->shouldReceive('load')->with(true)->andReturn(
            $settings2 = [
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        // reflects the order of the loader return values
        $expectedResolvedSettingsBag = new SettingsBag(
            [
                'core.key1' => 'eighteen',
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        $mockCache->shouldReceive('delete')->with('settings.bag.global')->once();
        $mockCache->shouldReceive('has')->with('settings.bag.global')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with(
            'settings.bag.global',
            \Mockery::type('Application\DeskPRO\NewSettings\SettingsBag')
        )->once();

        $this->assertEquals($expectedResolvedSettingsBag, $resolver->getGlobalSettings(true));
    }

    public function testDefaultSettingsIsFirstLoader()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

        $resolver = new SettingsResolver(
            [$mock1, $mock2], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $mock1->shouldReceive('load')->andReturn(
            $settings1 = [
                'core.key1' => 'eighteen',
                'core.key2' => 'sixteen',
                'core.key3' => 'number4',
            ]
        );

        $mock2->shouldReceive('load')->andReturn(
            $settings2 = [
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        // reflects the order of the loader return values
        $expectedDefaultSettingsBag = new SettingsBag(
            $settings1
        );

        $mockCache->shouldReceive('delete')->with('settings.bag.default')->never();
        $mockCache->shouldReceive('has')->with('settings.bag.default')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with(
            'settings.bag.default',
            \Mockery::type('Application\DeskPRO\NewSettings\SettingsBag')
        )->once();

        $this->assertEquals($expectedDefaultSettingsBag, $resolver->getDefaultSettings());
    }

    public function testDefaultSettingsCanForceReload()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

        $resolver = new SettingsResolver(
            [$mock1, $mock2], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $mock1->shouldReceive('load')->with(true)->andReturn(
            $settings1 = [
                'core.key1' => 'eighteen',
                'core.key2' => 'sixteen',
                'core.key3' => 'number4',
            ]
        );

        $mock2->shouldReceive('load')->with(true)->andReturn(
            $settings2 = [
                'core.key2' => 16,
                'core.key3' => 'some_new-string',
            ]
        );

        // reflects the order of the loader return values
        $expectedDefaultSettingsBag = new SettingsBag(
            $settings1
        );

        $mockCache->shouldReceive('delete')->with('settings.bag.default')->once();
        $mockCache->shouldReceive('has')->with('settings.bag.default')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with(
            'settings.bag.default',
            \Mockery::type('Application\DeskPRO\NewSettings\SettingsBag')
        )->once();

        $this->assertEquals($expectedDefaultSettingsBag, $resolver->getDefaultSettings(true));
    }

    public function testVirtualSettings()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock1->shouldReceive('load')->andReturn(['core.default_timezone' => 'non_virtual_val']);

        $resolver = new SettingsResolver(
            [$mock1], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $resolver->setVirtual(
            'core.default_timezone',
            function () {
                return 'func_generated_value';
            }
        );

        $mockCache->shouldReceive('has')->with('settings.bag.global')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with('settings.bag.global', \Mockery::any())->once();

        $this->assertEquals('func_generated_value', $resolver->getGlobalSettings()->get('core.default_timezone'));
    }

    public function testVirtualSettingsDefault()
    {
        $mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

        $mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
        $mock1->shouldReceive('load')->andReturn(['core.default_timezone' => 'non_virtual_val']);

        $resolver = new SettingsResolver(
            [$mock1], $mockCache,
            \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface')
        );

        $resolver->setVirtual(
            'core.default_timezone',
            function () {
                return 'func_generated_value';
            }
        );

        $mockCache->shouldReceive('has')->with('settings.bag.default')->andReturn(false)->once();
        $mockCache->shouldReceive('set')->with('settings.bag.default', \Mockery::any())->once();

        $this->assertEquals('func_generated_value', $resolver->getDefaultSettings()->get('core.default_timezone'));
    }
}
