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
use Application\DeskPRO\NewSettings\SettingsResolver;

class SettingsResolverTest extends \DpUnitTestCase
{
	public function testConstructedWithLoaders()
	{
		$mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
		$mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

		$loaders = array($mock1, $mock2);

		$resolver = new SettingsResolver($loaders, \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface'));

		$this->assertSame($loaders, $resolver->getLoaders(), 'stored with the same order');
	}

	public function testGlobalSettingsMergesLoadersAndCachesProperly()
	{
		$mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');

		$mock1 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');
		$mock2 = \Mockery::mock('Application\DeskPRO\NewSettings\SettingsLoaderInterface');

		$resolver = new SettingsResolver(array($mock1, $mock2), $mockCache);

		$mock1->shouldReceive('load')->andReturn($settings1 = array(
				'core.key1' => 'eighteen',
				'core.key2' => 'sixteen',
				'core.key3' => 'number4'
			)
		);

		$mock2->shouldReceive('load')->andReturn($settings2 = array(
				'core.key2' => 16,
				'core.key3' => 'some_new-string'
			)
		);

		// reflects the order of the loader return values
		$expectedResolvedSettingsBag = new SettingsBag(
			array(
				'core.key1' => 'eighteen',
				'core.key2' => 16,
				'core.key3' => 'some_new-string'
			)
		);

		$mockCache->shouldReceive('has')->with('settings.bag.global')->andReturn(false)->once();
		$mockCache->shouldReceive('set')->with('settings.bag.global', \Mockery::type('Application\DeskPRO\NewSettings\SettingsBag'))->once();

		$this->assertEquals($expectedResolvedSettingsBag, $resolver->getGlobalSettings());
	}
}
 