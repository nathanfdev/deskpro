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

namespace DpUnitTests\DeskPRO\NewSettings\Loader;


use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\NewSettings\Loader\ConfigPhpFileLoader;
use Mockery\Mock;

class ConfigPhpFileLoaderTest extends \DpUnitTestCase
{
	public function testInvalidCaseExceptions()
	{
		$loader = new ConfigPhpFileLoader('path/wont/be/found', $cache = new SimpleArrayCache());

		$this->setExpectedException('RuntimeException');

		$loader->load();
	}

	public function testLoadingWorksAndStoresInCache()
	{
		$config_file_path = __DIR__ . '/fixtures/configs_file.php';

		$expectedSettings = array(
			'key'       => 'val',
			'extra_key' => 'extra_val'
		);

		$cache_key = 'settings.loader.config_php_file.' . $config_file_path;

		$mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$mockCache->shouldReceive('has')->with($cache_key)->andReturn(false)->once();
		$mockCache->shouldReceive('set')->with($cache_key, $expectedSettings)->once();

		$loader = new ConfigPhpFileLoader($config_file_path, $mockCache);

		// asseritng that the correct array is recieved from the config file and that cache was set properly
		$this->assertSame(
			$expectedSettings,
			$loader->load()
		);
	}

	public function testLoadingUsesCacheIfExists()
	{
		$config_file_path = __DIR__ . '/fixtures/configs_file.php';

		$expectedSettings = array(
			'key'       => 'val',
			'extra_key' => 'extra_val'
		);

		$cache_key = 'settings.loader.config_php_file.' . $config_file_path;

		$mockCache = \Mockery::mock('Application\DeskPRO\Cache\CacheAdapterInterface');
		$mockCache->shouldReceive('has')->with($cache_key)->andReturn(true)->once();
		$mockCache->shouldReceive('get')->with($cache_key)->andReturn($expectedSettings)->once();

		$loader = new ConfigPhpFileLoader($config_file_path, $mockCache);

		// asseritng that the correct array is recieved from the cache
		$this->assertSame(
			$expectedSettings,
			$loader->load()
		);
	}
}
 