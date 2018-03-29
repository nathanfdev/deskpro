<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Application\NewSettings;

use Application\DeskPRO\NewSettings\SettingsBag;
use DpTest\DeskProTestCase;

class SettingsBagTest extends DeskProTestCase
{
    public function testIsLikeAnArrayObject()
    {
        $bag = new SettingsBag();

        $this->assertInstanceOf('\ArrayAccess', $bag);
        $this->assertInstanceOf('\IteratorAggregate', $bag);
        $this->assertInstanceOf('\Countable', $bag);
        $this->assertInstanceOf('\Serializable', $bag);
    }

    public function testEverythingAtOnceBecauseThisIsTrivial()
    {
        $inputArray = [
            'key'            => 'value',
            'setting'        => 2,
            'extra_setting'  => 0.9,
            't_bool_setting' => '1',
            'f_bool_setting' => '0',
            'serialized'     => serialize(['some']),
            'just_array'     => ['some'],
        ];

        $bag = new SettingsBag($inputArray);

        $this->assertEquals('value', $bag->get('key'));
        $this->assertEquals(2, $bag->get('setting'));
        $this->assertEquals(0.9, $bag->get('extra_setting'));

        $this->assertEquals(null, $bag->get('does_not_exist'));
        $this->assertEquals('default_works', $bag->get('does_not_exist', 'default_works'));

        $this->assertEquals('value', $bag['key']);
        $this->assertEquals(2, $bag['setting']);
        $this->assertEquals(0.9, $bag['extra_setting']);

        $this->assertSame(7, $bag->count());
        $this->assertSame(7, count($bag));

        $this->assertTrue($bag->has('key'));
        $this->assertFalse($bag->has('non_existant-key'));

        $this->assertTrue($bag->getBool('t_bool_setting'));
        $this->assertFalse($bag->getBool('f_bool_setting'));
        $this->assertFalse($bag->getBool('non_existant-key'));
        $this->assertTrue($bag->getBool('non_existant-key', '1'));

        $this->assertSame($bag->getSerializedArray('serialized'), ['some']);
        $this->assertSame($bag->getSerializedArray('just_array'), ['some']);

        $this->assertSame($inputArray, $bag->toArray(), 'can get the settings as an array');
    }

    public function testGroups()
    {
        $inputArray = [
            'no_group'               => 'value',
            'group_1.key'            => 'val',
            'group_1.extra_num'      => 2,
            'group_2.key'            => 7.8,
            'group_2.extra_num'      => 99,
            'group_2.deep.extra_num' => 301,
        ];

        $bag = new SettingsBag($inputArray);

        $this->assertEquals(
            [
                'key'       => 'val',
                'extra_num' => 2,
            ],
            $bag->getGroup('group_1')
        );

        $this->assertEquals(
            [
                'key'            => 7.8,
                'extra_num'      => 99,
                'deep.extra_num' => 301,
            ],
            $bag->getGroup('group_2')
        );

        $this->assertEquals(
            [
                'extra_num' => 301,
            ],
            $bag->getGroup('group_2.deep')
        );

        $this->assertEquals(
            [
                'group_2.deep.extra_num' => 301,
            ],
            $bag->getGroup('group_2.deep', false)
        );
    }
}
