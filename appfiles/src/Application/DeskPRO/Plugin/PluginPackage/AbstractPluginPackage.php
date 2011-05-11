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
	 * Get an array of PluginListener objects required for this plugin.
	 *
	 * @return \Application\DeskPRO\Entity\PluginListener[]
	 */
	abstract public static function getPluginListeners();

	/**
	 * Called the first time the plugin is installed.
	 *
	 * @return void
	 */
	abstract public static function install(Plugin $plugin);

	/**
	 * Called whent he plugin exists in the database, but the source
	 * is a newer version.
	 *
	 * @param Plugin $plugin The existing plugin (ie use this to get version)
	 * @return void
	 */
	abstract public static function upgrade(Plugin $plugin);

	/**
	 * Called when the plugin is removed.
	 *
	 * @return void
	 */
	abstract public static function uninstall(Plugin $plugin);

	/**
	 * Called when the plugin is enabled
	 *
	 * @return void
	 */
	abstract public static function enable(Plugin $plugin);

	/**
	 * Called whent he plugin is disabled
	 *
	 * @return void
	 */
	abstract public static function deactivate(Plugin $plugin);

	
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
	 * Get paths to auto-load
	 * 
	 * @return array
	 */
	public static function getAutoloadPaths()
	{
		$plugin_namespace = Util::getClassNamespace(get_called_class());
		$plugin_path = dirname(__FILE__);

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