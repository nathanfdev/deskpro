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
 * @subpackage
 */

namespace DpUnitTests\DeskPRO\Cache;


use Application\DeskPRO\Cache\ConvenientCache;

class ConvenientCacheTest extends \DpUnitTestCase
{
	public function testGetWillUseAndSetDefaultIfNoCacheEntryExists()
	{
		$adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
		$adapter->shouldReceive('set')->with('key', 'some_val')->once();

		$cache = new ConvenientCache($adapter);
		$val = $cache->get('key', 'some_val');

		$this->assertEquals('some_val', $val);
	}

	public function testGetDefaultCanBeAnyClosure()
	{
		$adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
		$adapter->shouldReceive('set')->with('key', 'some_val')->once();

		$cache = new ConvenientCache($adapter);
		$val = $cache->get('key', function () {
				return 'some_val';
			}
		);

		$this->assertEquals('some_val', $val);
	}

	public function testGetDefaultCanBeAnyCallable()
	{
		$adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$adapter->shouldReceive('has')->with('key')->andReturn(false)->once();
		$adapter->shouldReceive('set')->with('key', 'some_val')->once();

		$cache = new ConvenientCache($adapter);
		$val = $cache->get('key', array($this, 'getReturnVal'));

		$this->assertEquals('some_val', $val);
	}

	public function getReturnVal()
	{
		return 'some_val';
	}

	public function testGEtCallableDefaultDoesNotInterfereWithRealValues()
	{
		$adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$adapter->shouldReceive('has')->with('key')->andReturn(false)->once();

		$not_callable = array('some_key_that_is_not_a_class', 'getReturnVal');

		$adapter->shouldReceive('set')->with('key', $not_callable)->once();

		$cache = new ConvenientCache($adapter);
		$val = $cache->get('key', $not_callable);

		$this->assertEquals($not_callable, $val);
	}

	public function testGEtCallableDefaultDoesNotInterfereWithRealValues2()
	{
		$adapter = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$adapter->shouldReceive('has')->with('key')->andReturn(false)->once();

		$not_callable = 'someNonExistantClass::noMethod';

		$adapter->shouldReceive('set')->with('key', $not_callable)->once();

		$cache = new ConvenientCache($adapter);
		$val = $cache->get('key', $not_callable);

		$this->assertEquals($not_callable, $val);
	}
}
 