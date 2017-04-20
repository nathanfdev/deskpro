<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\StringUtils;
use DpTest\DeskProTestCase;

class StringUtilsTest extends DeskProTestCase
{
    /**
     * @dataProvider getTestToSnakeCase
     */
    public function testToSnakeCase($input, $expected)
    {
        $this->assertEquals(StringUtils::toSnakeCase($input), $expected);
    }

    public function getTestToSnakeCase()
    {
        return [
            ['DeskPRO', 'desk_pro'],
            ['ImACamel', 'im_a_camel'],
            ['QuickBrownFoxJumpsOverTheLazyDog', 'quick_brown_fox_jumps_over_the_lazy_dog'],
        ];
    }

    /**
     * @dataProvider getTestToCamelCase
     */
    public function testToCamelCase($expected, $input)
    {
        $this->assertEquals(StringUtils::toCamelCase($input), $expected);
    }

    public function getTestToCamelCase()
    {
        return [
            ['DeskPro', 'desk_pro'],
            ['ImACamel', 'im_a_camel'],
            ['QuickBrownFoxJumpsOverTheLazyDog', 'quick_brown_fox_jumps_over_the_lazy_dog'],
        ];
    }

    /**
     * @dataProvider getTestStartsWith
     */
    public function testStartsWith($needle, $haystack, $ignoreCase, $expected)
    {
        $this->assertEquals(StringUtils::startsWith($needle, $haystack, $ignoreCase), $expected);
    }

    public function getTestStartsWith()
    {
        // $needle, $haystack, $ignoreCase, $expected
        return [
            ['foo', 'foobar', false, true],
            ['foo', 'foo', false, true],
            ['ABC', 'abcde', false, false],
            ['ABC', 'abcde', true, true],
            ['123', '123', false, true],
            ['foo', ' foobar', false, false],
            ['', 'x', false, false],
            ['x', '', false, false],
        ];
    }

    /**
     * @dataProvider getTestEndsWith
     */
    public function testEndsWith($needle, $haystack, $ignoreCase, $expected)
    {
        $this->assertEquals(StringUtils::endsWith($needle, $haystack, $ignoreCase), $expected);
    }

    public function getTestEndsWith()
    {
        // $needle, $haystack, $ignoreCase, $expected
        return [
            ['bar', 'foobar', false, true],
            ['foo', 'foo', false, true],
            ['CDE', 'abcde', false, false],
            ['CDE', 'abcde', true, true],
            ['123', '123', false, true],
            ['bar', 'foobar ', false, false],
            ['', 'x', false, false],
            ['x', '', false, false],
        ];
    }
}
