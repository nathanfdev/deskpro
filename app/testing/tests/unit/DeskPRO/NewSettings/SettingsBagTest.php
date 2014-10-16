<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage NewSettings
 */

namespace DpUnitTests\DeskPRO\NewSettings;


use Application\DeskPRO\NewSettings\SettingsBag;

class SettingsBagTest extends \DpUnitTestCase
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
		$inputArray = array(
			'key'           => 'value',
			'setting'       => 2,
			'extra_setting' => 0.9
		);

		$bag = new SettingsBag($inputArray);

		$this->assertEquals('value', $bag->get('key'));
		$this->assertEquals(2, $bag->get('setting'));
		$this->assertEquals(0.9, $bag->get('extra_setting'));

		$this->assertEquals(null, $bag->get('does_not_exist'));
		$this->assertEquals('default_works', $bag->get('does_not_exist', 'default_works'));

		$this->assertEquals('value', $bag['key']);
		$this->assertEquals(2, $bag['setting']);
		$this->assertEquals(0.9, $bag['extra_setting']);

		$this->assertSame(3, $bag->count());
		$this->assertSame(3, count($bag));

		$this->assertTrue($bag->has('key'));
		$this->assertFalse($bag->has('non_existant-key'));

		$this->assertSame($inputArray, $bag->toArray(), 'can get the settings as an array');
	}

	public function testGroups()
	{
		$inputArray = array(
			'no_group'          => 'value',
			'group_1.key'       => 'val',
			'group_1.extra_num' => 2,
			'group_2.key'       => 7.8,
			'group_2.extra_num' => 99,
			'group_2.deep.extra_num' => 301
		);

		$bag = new SettingsBag($inputArray);

		$this->assertEquals(
			array(
				'key' => 'val',
				'extra_num' => 2
			),
			$bag->getGroup('group_1')
		);

		$this->assertEquals(
			array(
				'key' => 7.8,
				'extra_num' => 99,
				'deep.extra_num' => 301
			),
			$bag->getGroup('group_2')
		);

		$this->assertEquals(
			array(
				'extra_num' => 301
			),
			$bag->getGroup('group_2.deep')
		);

		$this->assertEquals(
			array(
				'group_2.deep.extra_num' => 301
			),
			$bag->getGroup('group_2.deep', false)
		);
	}
}
 