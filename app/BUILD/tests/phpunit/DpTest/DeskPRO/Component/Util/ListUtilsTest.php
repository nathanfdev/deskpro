<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\TypeUtils;
use DpTest\DeskProTestCase;

class ListUtilsTest extends DeskProTestCase
{
    public function testFilterOutFalsey()
    {
        $this->assertEquals(
            array(1, 2, 3),
            ListUtils::filterOutFalsey(array(false, 1, 2, null, 3))
        );

        $this->assertEquals(
            array(1, 2, 3),
            ListUtils::filterOutFalsey(array('a' => false, 55 => 1, 'b' => 2, 0 => null, 0, 'c' => 3))
        );

        $this->assertEquals(
            array(),
            ListUtils::filterOutFalsey(array(false, null, 0, ''))
        );
    }

    public function testFilterOutValues()
    {
        $this->assertEquals(
            array(false, 2, null, 3),
            ListUtils::filterOutValues(array(false, 1, 2, null, 3), 1)
        );

        $this->assertEquals(
            array(1, 2, null, 3),
            ListUtils::filterOutValues(array(false, 1, 2, null, 3), false)
        );

        $this->assertEquals(
            array(1, 2, 3),
            ListUtils::filterOutValues(array(false, 1, 2, null, 3), false, false)
        );
    }

    public function testUnique()
    {
        $this->assertEquals(
            array(false, 2, 3),
            ListUtils::unique(array(false, 2, null, 3, false, null, 3, '3'), '==')
        );

        $this->assertEquals(
            array(false, 2, null, 3, '3'),
            ListUtils::unique(array(false, 2, null, 3, false, null, 3, '3'), '===')
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
}
