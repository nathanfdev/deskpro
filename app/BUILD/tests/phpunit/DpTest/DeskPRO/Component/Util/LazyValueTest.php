<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\LazyValue;
use DpTest\DeskProTestCase;

class LazyValueTest extends DeskProTestCase
{
    public function testLazy()
    {
        $val = new LazyValue(function ($prev) {
            return ($prev ?: 0) + 1;
        });

        $this->assertFalse($val->isLoaded());
        $this->assertEquals(1, $val());
        $this->assertTrue($val->isLoaded());

        $this->assertEquals(1, $val());

        $val->reload();
        $this->assertEquals(2, $val());
    }
}
