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
 * @subpackage Plugins
 */

namespace Application\DeskPRO\Plugin\PluginPackage;

use Application\DeskPRO\Entity\Plugin;
use Orb\Util\Util;

abstract class AbstractPluginPackage
{
	/**
	 * Called the first time the plugin is installed.
	 *
	 * @return InstallerAbstract
	 */
	public static function getInstaller($install_controller, Plugin $plugin)
	{
		$installer = new InstallerSimple($plugin, $install_controller);
		return $installer;
	}

	/**
	 * Called whent he plugin exists in the database, but the source
	 * is a newer version.
	 *
	 * @param Plugin $plugin The existing plugin (ie use this to get version)
	 * @return void
	 */
	public static function getUpgrader($upgrade_controller, Plugin $plugin)
	{

	}

	/**
	 * Called when the plugin is removed.
	 *
	 * @param Plugin $plugin The existing plugin (ie use this to get version)
	 * @return UninstallerAbstract
	 */
	public static function getUninstaller($uninstall_controller, Plugin $plugin)
	{
		$installer = new UninstallerSimple($plugin, $uninstall_controller);
		return $installer;
	}

	public static function renderConfig($controller, Plugin $plugin)
	{
		if ($controller->in->getBool('prcoess')) {
			$controller->ensureRequestToken();

			return $controller->redirectRoute('admin_plugins');
		}

		return $controller->render(static::getName() . ':Admin:config.html.twig');
	}

	public static function isAvailable()
	{
		return true;
	}

	
	/**
	 * Ge tthe version
	 *
	 * @return mixed
	 */
	public static function getVersion()
	{
		return '1';
	}

	public static function getBasePluginPath()
	{
		return str_replace('/', DIRECTORY_SEPARATOR, DP_WEB_ROOT) . DIRECTORY_SEPARATOR . 'plugins';
	}

	public static function getRelativePluginPath($plugin_path)
	{
		return str_replace(self::getBasePluginPath(), '%PLUGINS%', $plugin_path);
	}
	
	/**
	 * Get the path to the Resources directory
	 *
	 * @return string
	 */
	public static function getResourcesPath()
	{
		$plugin_path = dirname(Util::getClassFilename(get_called_class()));
		return self::getRelativePluginPath($plugin_path) . '/Resources';
	}


	/**
	 * Get paths to auto-load. The namespace fallbacks handle most cases.
	 * 
	 * @return array
	 */
	public static function getAutoloadPaths()
	{
		return array();
	}

	
	/**
	 * Get the unique name for the plugin
	 * 
	 * @return string
	 */
	public static function getName()
	{
		return str_replace('\\', '_', Util::getClassNamespace(get_called_class()));
	}


	/**
	 * Get the readable title for this plugin
	 *
	 * @return string
	 */
	public static function getTitle()
	{
		return ucwords(str_replace('\\', ' ', Util::getClassNamespace(get_called_class())));
	}


	/**
	 * Get the readable description for this plugin
	 * 
	 * @return string
	 */
	public static function getDescription()
	{
		return '';
	}
}
