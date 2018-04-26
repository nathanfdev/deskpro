<?php

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

    public function testRemoveFromStart()
    {
        $this->assertEquals('baz', StringUtils::removeFromStart('foo.bar.', 'foo.bar.baz'));
        $this->assertEquals(null, StringUtils::removeFromStart('FOO.BAR.', 'foo.bar.baz'));
        $this->assertEquals('baz', StringUtils::removeFromStart('FOO.BAR.', 'foo.bar.baz', true));
        $this->assertEquals(null, StringUtils::removeFromStart('loo.bar.', 'foo.bar.baz'));
    }
}
