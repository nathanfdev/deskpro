<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Plugins
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

	
	/**
	 * Get the path to the Resources directory
	 *
	 * @return string
	 */
	public static function getResourcesPath()
	{
		$plugin_path = dirname(Util::getClassFilename(get_called_class()));
		$path = str_replace(DP_ROOT.'/plugins', '', $plugin_path);
		$path .= '/Resources/';

		return $path;
	}


	/**
	 * Get paths to auto-load
	 * 
	 * @return array
	 */
	public static function getAutoloadPaths()
	{
		$plugin_namespace = Util::getClassNamespace(get_called_class());
		$plugin_path = dirname(dirname(dirname(Util::getClassFilename(get_called_class()))));
		$plugin_path = str_replace(DP_ROOT.'/plugins', '', $plugin_path);

		return array($plugin_namespace => $plugin_path);
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