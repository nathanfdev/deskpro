<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\Cache\Adapter;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use DpTest\DeskProTestCase;

class SimpleArrayCacheTest extends DeskProTestCase
{
    public function testCacheWorks()
    {
        $cache = new SimpleArrayCache();

        $this->assertFalse($cache->has('key'));

        $cache->set('key', $arr = ['some' => 'data']);

        $this->assertSame($arr, $cache->get('key'));
        $this->assertTrue($cache->has('key'));

        $this->assertNull($cache->get('non_existant-key'));
    }
}
