<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util;

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StructComparer;
use DeskPRO\Component\Util\TypeUtils;
use DpTest\DeskProTestCase;

class ListUtilsTest extends DeskProTestCase
{
    public function testFilterOutFalsey()
    {
        $this->assertEquals(
            [1, 2, 3],
            ListUtils::filterOutFalsey([false, 1, 2, null, 3])
        );

        $this->assertEquals(
            [1, 2, 3],
            ListUtils::filterOutFalsey(['a' => false, 55 => 1, 'b' => 2, 0 => null, 0, 'c' => 3])
        );

        $this->assertEquals(
            [],
            ListUtils::filterOutFalsey([false, null, 0, ''])
        );
    }

    public function testFilterOutValues()
    {
        $this->assertEquals(
            [false, 2, null, 3],
            ListUtils::filterOutValues([false, 1, 2, null, 3], 1)
        );

        $this->assertEquals(
            [1, 2, null, 3],
            ListUtils::filterOutValues([false, 1, 2, null, 3], false)
        );

        $this->assertEquals(
            [1, 2, 3],
            ListUtils::filterOutValues([false, 1, 2, null, 3], false, false)
        );
    }

    public function testUnique()
    {
        $this->assertEquals(
            [false, 2, 3],
            ListUtils::unique([false, 2, null, 3, false, null, 3, '3'], '==')
        );

        $this->assertEquals(
            [false, 2, null, 3, '3'],
            ListUtils::unique([false, 2, null, 3, false, null, 3, '3'], '===')
        );
    }

    public function testIsList()
    {
        $this->assertTrue(TypeUtils::isList([1, 2, 3, 4, 'a', 'b', 'c']));
        $this->assertTrue(TypeUtils::isList([]));
        $this->assertTrue(TypeUtils::isList(new \SplFixedArray()));
        $this->assertTrue(TypeUtils::isList(new \SplStack()));
        $this->assertTrue(TypeUtils::isList(new \SplQueue()));
        $this->assertTrue(TypeUtils::isList(new \SplDoublyLinkedList()));

        $this->assertFalse(TypeUtils::isList(['a', 'b' => 'c', 'd']));
        $this->assertFalse(TypeUtils::isList([0 => 1, 1 => 1, 3 => 2]));
        $this->assertFalse(TypeUtils::isList(['a' => 1, 'b' => 2]));
    }

    public function testCollectionFinder()
    {
        $arr = [
            ['id' => 'a', 'foo' => 'bar'],
            ['id' => 'a', 'foo' => 'baz'],
            ['id' => 'b', 'foo' => 'pop'],
        ];

        $this->assertEquals(
            [['id' => 'a', 'foo' => 'bar'], ['id' => 'a', 'foo' => 'baz']],
            ListUtils::filter($arr, StructComparer::byKey('id', 'a'))
        );

        $this->assertEquals(
            ['id' => 'b', 'foo' => 'pop'],
            ListUtils::first($arr, StructComparer::byKey('id', 'b'))
        );
    }
}
