<?php
namespace DpUnitTests\DeskPRO\Util;

use Application\DeskPRO\Util\InfLoopAssert;

class InfLoopAssertTest extends \DpUnitTestCase
{
    public function testOk()
    {
        for ($i = 0; $i < 100; $i++) {
            InfLoopAssert::count($this, 100, "This sholud be ok -- $i", true);
        }
    }

    public function testResetOk()
    {
        InfLoopAssert::reset($this);
        for ($i = 0; $i < 100; $i++) {
            InfLoopAssert::count($this, 100, "This sholud be ok -- $i", true);
        }

        InfLoopAssert::reset($this);
        for ($i = 0; $i < 100; $i++) {
            InfLoopAssert::count($this, 100, "This sholud be ok -- $i", true);
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testResetException()
    {
        InfLoopAssert::reset($this);
        for ($i = 0; $i < 100; $i++) {
            InfLoopAssert::count($this, 100, "This sholud be ok -- $i", true);
        }

        InfLoopAssert::reset($this);
        for ($i = 0; $i < 101; $i++) {
            InfLoopAssert::count($this, 100, "This sholud fail -- $i", true);
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testException()
    {
        InfLoopAssert::reset($this);
        for ($i = 0; $i < 101; $i++) {
            InfLoopAssert::count($this, 100, "This sholud fail -- $i", true);
        }
    }
}
