<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
            [1 => 1, 2 => 2, 4 => 3],
            MapUtils::filterOutFalsey([false, 1, 2, null, 3])
        );

        $this->assertEquals(
            [55 => 1, 'b' => 2, 'c' => 3],
            MapUtils::filterOutFalsey(['a' => false, 55 => 1, 'b' => 2, 0 => null, 0, 'c' => 3])
        );

        $this->assertEquals(
            [],
            MapUtils::filterOutFalsey([false, null, 0, ''])
        );
    }

    public function testFilterOutValues()
    {
        $this->assertEquals(
            ['a' => false, 'c' => 2, 'd' => null, 'e' => 3],
            MapUtils::filterOutValues(['a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3], 1)
        );

        $this->assertEquals(
            ['b' => 1, 'c' => 2, 'd' => null, 'e' => 3],
            MapUtils::filterOutValues(['a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3], false)
        );

        $this->assertEquals(
            ['b' => 1, 'c' => 2, 'e' => 3],
            MapUtils::filterOutValues(['a' => false, 'b' => 1, 'c' => 2, 'd' => null, 'e' => 3], false, false)
        );
    }

    public function testPrependItem()
    {
        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            MapUtils::prependItem(['b' => 2, 'c' => 3], 'a', 1)
        );

        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            MapUtils::prependItem(['b' => 2, 'c' => 3, 'a' => 'XXX'], 'a', 1)
        );
    }

    public function testRekeyByFn()
    {
        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            MapUtils::rekeyByFn([1, 2, 3], function ($v, $k) {
                $keys = 'abc';

                return $keys[$v - 1];
            })
        );

        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            MapUtils::rekeyByFn([1, 2, 3], function ($v, $k) {
                $keys = 'abc';

                return $keys[$k];
            })
        );
    }

    public function testRekeyByKey()
    {
        $this->assertEquals(
            ['a' => ['a', 1], 'b' => ['b', 2], 'c' => ['c', 3]],
            MapUtils::rekeyByFn([
                ['a', 1],
                ['b', 2],
                ['c', 3],
            ], function ($v, $k) {
                return $v[0];
            })
        );
    }

    public function testRekeyByGetter()
    {
        $a = new POJOExample('a', 'foo');
        $b = new POJOExample('b', 'bar');

        $in  = [$a, $b];
        $out = ['a' => $a, 'b' => $b];

        $this->assertEquals($out, MapUtils::rekeyByGetter($in, 'getId'));
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

    public function testRecursiveDiff()
    {
        $arr1 = ['a' => 'b', 'foo' => 'bar', 'baz' => ['z' => 'zz', 'y' => 'yy']];
        $arr2 = ['foo' => 'zzz', 'baz' => ['y' => 'yy'], 'a' => 'b'];

        $this->assertEquals(
            ['foo' => 'bar', 'baz' => ['z' => 'zz']],
            MapUtils::recursiveDiff($arr1, $arr2)
        );
    }
}

class POJOExample
{
    private $id;
    private $val;

    /**
     * POJOExample constructor.
     *
     * @param $id
     * @param $val
     */
    public function __construct($id, $val)
    {
        $this->id  = $id;
        $this->val = $val;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getVal()
    {
        return $this->val;
    }

    /**
     * @param mixed $val
     */
    public function setVal($val)
    {
        $this->val = $val;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "{$this->id}:{$this->val}";
    }
}
