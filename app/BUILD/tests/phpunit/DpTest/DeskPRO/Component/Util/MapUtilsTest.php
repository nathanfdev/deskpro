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

use DeskPRO\Component\Util\MapUtils;
use DpTest\DeskProTestCase;

class MapUtilsTest extends DeskProTestCase
{
    public function testFilterOutFalsey()
    {
        $this->assertEquals(
            array(1 => 1, 2 => 2, 4 => 3),
            MapUtils::filterOutFalsey(array(false, 1, 2, null, 3))
        );

        $this->assertEquals(
            array(55 => 1, 'b' => 2, 'c' => 3),
            MapUtils::filterOutFalsey(array('a' => false, 55 => 1, 'b' => 2, 0 => null, 0, 'c' => 3))
        );

        $this->assertEquals(
            array(),
            MapUtils::filterOutFalsey(array(false, null, 0, ''))
        );
    }

    public function testFilterOutValues()
    {
        $this->assertEquals(
            array('a' => false, 'c' => 2, 'd' => null, 'e' => 3),
            MapUtils::filterOutValues(array('a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3), 1)
        );

        $this->assertEquals(
            array('b' => 1, 'c' => 2, 'd' => null, 'e' => 3),
            MapUtils::filterOutValues(array('a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3), false)
        );

        $this->assertEquals(
            array('b' => 1, 'c' => 2, 'e' => 3),
            MapUtils::filterOutValues(array('a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3), false, false)
        );
    }

    public function testPrependItem()
    {
        $this->assertEquals(
            array('a' => 1, 'b' => 2, 'c' => 3),
            MapUtils::prependItem(array('b' => 2, 'c' => 3), 'a', 1)
        );

        $this->assertEquals(
            array('a' => 1, 'b' => 2, 'c' => 3),
            MapUtils::prependItem(array('b' => 2, 'c' => 3, 'a' => 'XXX'), 'a', 1)
        );
    }

    public function testRekeyByFn()
    {
        $this->assertEquals(
            array('a' => 1, 'b' => 2, 'c' => 3),
            MapUtils::rekeyByFn(array(1, 2, 3), function ($v, $k) {
                $keys = 'abc';

                return $keys[$v - 1];
            })
        );

        $this->assertEquals(
            array('a' => 1, 'b' => 2, 'c' => 3),
            MapUtils::rekeyByFn(array(1, 2, 3), function ($v, $k) {
                $keys = 'abc';

                return $keys[$k];
            })
        );
    }

    public function testRekeyByKey()
    {
        $this->assertEquals(
            array('a' => array('a', 1), 'b' => array('b', 2), 'c' => array('c', 3)),
            MapUtils::rekeyByFn(array(
                array('a', 1),
                array('b', 2),
                array('c', 3),
            ), function ($v, $k) {
                return $v[0];
            })
        );
    }

    public function testGetInStr()
    {
        $this->assertEquals(
            'x',
            MapUtils::getIn(['foo' => ['bar' => ['baz' => 'x', 'boo' => 'gert']]], 'foo.bar.baz')
        );
    }

    public function testGetIn()
    {
        $this->assertEquals(
            'x',
            MapUtils::getIn(['foo' => ['bar' => ['baz' => 'x', 'boo' => 'gert']]], ['foo', 'bar', 'baz'])
        );
    }

    public function testSetInStr()
    {
        $arr1 = ['foo' => ['bar' => ['baz' => 'x', 'boo' => 'gert']], 'other' => ['value' => 1]];
        $arr2 = ['foo' => ['bar' => ['baz' => 'z', 'boo' => 'gert']], 'other' => ['value' => 1]];

        $this->assertEquals(
            $arr2,
            MapUtils::setIn($arr1, 'foo.bar.baz', 'z')
        );
    }

    public function testSetIn()
    {
        $arr1 = ['foo' => ['bar' => ['baz' => 'x', 'boo' => 'gert']], 'other' => ['value' => 1]];
        $arr2 = ['foo' => ['bar' => ['baz' => 'z', 'boo' => 'gert']], 'other' => ['value' => 1]];

        $this->assertEquals(
            $arr2,
            MapUtils::setIn($arr1, ['foo', 'bar', 'baz'], 'z')
        );
    }
}
