<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\RandUtils;
use DpTest\DeskProTestCase;

class RandUtilsTest extends DeskProTestCase
{
    public function testRandStringLen()
    {
        $this->assertEquals(8, strlen(RandUtils::randomString()));
        $this->assertEquals(10, strlen(RandUtils::randomString(10)));
        $this->assertEquals(1, strlen(RandUtils::randomString(1)));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRandStringInvaidLen()
    {
        RandUtils::randomString(0);
    }

    public function testRandStringChars()
    {
        $this->assertEquals('zzzz', RandUtils::randomString(4, 'z'));
        $this->assertRegExp('#^[a-zA-Z0-9]{10}$#', RandUtils::randomString(10, 'alphanum'));
        $this->assertRegExp('#^[a-z0-9]{10}$#', RandUtils::randomString(10, 'alphanum_i'));
        $this->assertRegExp('#^[A-Z0-9]{10}$#', RandUtils::randomString(10, 'alphanum_iu'));
        $this->assertRegExp('#^[0-9]{10}$#', RandUtils::randomString(10, 'num'));
        $this->assertRegExp('#^[a-zA-Z]{10}$#', RandUtils::randomString(10, 'alpha'));
        $this->assertRegExp('#^[a-z]{10}$#', RandUtils::randomString(10, 'alpha_i'));
        $this->assertRegExp('#^[A-Z]{10}$#', RandUtils::randomString(10, 'alpha_iu'));
    }

    public function testRandStringFormat()
    {
        $this->assertRegExp('#^[A-Z]{10}$#', RandUtils::randomStringFormat('%3A%3A%4A'));
    }
}
