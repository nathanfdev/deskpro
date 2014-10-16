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


	public function getGlobalSettings()
	{
		return $this->cache->get('settings.bag.global', array($this, 'computeGlobalSettings'));
	}


	/**
	 * @return SettingsBag
	 */
	public function computeGlobalSettings()
	{
		$global_settings_array = array();

		foreach ($this->loaders as $loader) {
			$global_settings_array = array_merge($global_settings_array, $loader->load());
		}

		return new SettingsBag($global_settings_array);
	}
}
 