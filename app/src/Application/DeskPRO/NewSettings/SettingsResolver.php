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

namespace Application\DeskPRO\NewSettings;


use Application\DeskPRO\Cache\CacheAdapterInterface;
use Application\DeskPRO\Cache\ConvenientCache;

class SettingsResolver
{
	const CACHE_KEY_GLOBAL = 'settings.bag.global';
	const CACHE_KEY_DEFAULT = 'settings.bag.default';

	/**
	 * @var SettingsLoaderInterface[]
	 */
	private $loaders;

	/**
	 * @var \Application\DeskPRO\Cache\ConvenientCache
	 */
	private $cache;


	public function __construct(array $loaders, CacheAdapterInterface $cache)
	{
		$this->loaders = $loaders;
		$this->cache = new ConvenientCache($cache);
	}

	public function getLoaders()
	{
		return $this->loaders;
	}


	public function getGlobalSettings($force = false)
	{
		if ($force) {
			$this->cache->delete(static::CACHE_KEY_GLOBAL);
		}

		$that = $this;

		return $this->cache->get(
			static::CACHE_KEY_GLOBAL,
			function () use ($that, $force) {
				$global_settings_array = array();

				foreach ($that->getLoaders() as $loader) {
					$global_settings_array = array_merge($global_settings_array, $loader->load($force));
				}

				return new SettingsBag($global_settings_array);
			}
		);
	}


	public function getDefaultSettings($force = false)
	{
		if ($force) {
			$this->cache->delete(static::CACHE_KEY_DEFAULT);
		}

		$that = $this;

		return $this->cache->get(
			static::CACHE_KEY_DEFAULT,
			function () use ($that, $force) {

				$default_settings = array();

				$loaders = $that->getLoaders();
				if (count($loaders) > 0) {
					$default_loader = $loaders[0];
					$default_settings = $default_loader->load($force);
				}

				return new SettingsBag($default_settings);
			}
		);
	}
}
 