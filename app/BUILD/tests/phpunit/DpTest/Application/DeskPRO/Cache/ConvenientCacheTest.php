<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Cache;

use Application\DeskPRO\Cache\ConvenientCache;
use DpTest\DeskProTestCase;

class ConvenientCacheTest extends DeskProTestCase
{
    public function testGetWillUseAndSetDefaultIfNoCacheEntryExists()
    {
        $adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
        $adapter->shouldReceive('set')->with('key', 'some_val')->once();

        $cache = new ConvenientCache($adapter);
        $val   = $cache->get('key', 'some_val');

        $this->assertEquals('some_val', $val);
    }

    public function testGetDefaultCanBeAnyClosure()
    {
        $adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
        $adapter->shouldReceive('set')->with('key', 'some_val')->once();

        $cache = new ConvenientCache($adapter);
        $val   = $cache->get(
            'key',
            function () {
                return 'some_val';
            }
        );

        $this->assertEquals('some_val', $val);
    }

    public function testGetDefaultCanBeAnyCallable()
    {
        $adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
        $adapter->shouldReceive('set')->with('key', 'some_val')->once();

        $cache = new ConvenientCache($adapter);
        $val   = $cache->get('key', [$this, 'getReturnVal']);

        $this->assertEquals('some_val', $val);
    }

    public function getReturnVal()
    {
        return 'some_val';
    }

    public function testGEtCallableDefaultDoesNotInterfereWithRealValues()
    {
        $adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $adapter->shouldReceive('has')->with('key')->andReturn(false)->once();

        $not_callable = ['some_key_that_is_not_a_class', 'getReturnVal'];

        $adapter->shouldReceive('set')->with('key', $not_callable)->once();

        $cache = new ConvenientCache($adapter);
        $val   = $cache->get('key', $not_callable);

        $this->assertEquals($not_callable, $val);
    }

    public function testGEtCallableDefaultDoesNotInterfereWithRealValues2()
    {
        $adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
        $adapter->shouldReceive('has')->with('key')->andReturn(false)->once();

        $not_callable = 'someNonExistantClass::noMethod';

        $adapter->shouldReceive('set')->with('key', $not_callable)->once();

        $cache = new ConvenientCache($adapter);
        $val   = $cache->get('key', $not_callable);

        $this->assertEquals($not_callable, $val);
    }
}
