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
