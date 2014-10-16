<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\NewSettings\Loader\DbGlobalSettingsTableLoader;
use Application\DeskPRO\NewSettings\Loader\GlobalsArrayLoader;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;

class SettingsResolverService
{
	public static function create(DeskproContainer $container)
	{
		/** @var \Application\DeskPRO\Cache\Adapter\SimpleArrayCache $simple_array_cache */
		$simple_array_cache = $container->get('cache.simple_array');

		// loaders, in proper order. first loader is default settings.
		$loaders = array(
			$container->getSystemService('default_settings_loader'),
		    new DbGlobalSettingsTableLoader($container->getEm()->getConnection(), $simple_array_cache),
			new GlobalsArrayLoader($simple_array_cache)
		);

		$resolver = new SettingsResolver($loaders, $simple_array_cache);

		// virtual settings
		$resolver->setVirtual(
			'core.interact_require_login', function (array $settings) {
				$settings = new SettingsBag($settings);
				return !$settings->get('core.reg_enabled') || $settings->get('core.reg_required');
			}
		);
		$resolver->setVirtual(
			'default_timezone', function ($settings) {
				$settings = new SettingsBag($settings);

				try {
					$timezone_string = $settings->get('core.default_timezone');
					$tz = new \DateTimeZone($timezone_string);
				} catch (\Exception $e) {
					$tz = new \DateTimeZone('UTC');
				}

				return $tz;
			}
		);

		return $resolver;
	}
}
