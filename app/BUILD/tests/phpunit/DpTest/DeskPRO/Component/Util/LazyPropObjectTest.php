<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\LazyPropObject;
use DpTest\DeskProTestCase;

class LazyPropObjectTest extends DeskProTestCase
{
    public function testLazy()
    {
        $obj = new LazyPropObject(['val' => function ($props, $prev) {
            return ($prev ?: 0) + 1;
        }]);

        $this->assertTrue($obj->isDefined('val'));
        $this->assertFalse($obj->isDefined('foobar'));

        $this->assertFalse($obj->isLoaded('val'));
        $this->assertEquals(1, $obj->val);
        $this->assertTrue($obj->isLoaded('val'));
        $this->assertEquals(1, $obj->get('val'));

        $obj->reload('val');
        $this->assertEquals(2, $obj->val);
    }
}
