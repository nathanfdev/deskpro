<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DeskproPlugins
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskproPlugins\Highrise;

use Application\DeskPRO\Plugin\PluginPackage\AbstractPluginPackage;

class PluginPackage extends AbstractPluginPackage
{
	/**
	 * Called whent he plugin is disabled
	 *
	 * @return void
	 */
	public static function deactivate(Plugin $plugin)
	{

	}

	/**
	 * Called when the plugin is enabled
	 *
	 * @return void
	 */
	public static function enable(Plugin $plugin)
	{

	}

	/**
	 * Get an array of PluginListener objects required for this plugin.
	 *
	 * @return \Application\DeskPRO\Entity\PluginListener[]
	 */
	public static function getPluginListeners()
	{

	}

	/**
	 * Called the first time the plugin is installed.
	 *
	 * @return void
	 */
	public static function install(Plugin $plugin)
	{

	}

	/**
	 * Called when the plugin is removed.
	 *
	 * @return void
	 */
	public static function uninstall(Plugin $plugin)
	{

	}

	/**
	 * Called whent he plugin exists in the database, but the source
	 * is a newer version.
	 *
	 * @param Plugin $plugin The existing plugin (ie use this to get version)
	 * @return void
	 */
	public static function upgrade(Plugin $plugin)
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
	 * Get the unique name for the plugin
	 *
	 * @return string
	 */
	public static function getName()
	{
		return 'dp_highrise';
	}


	/**
	 * Get the readable title for this plugin
	 *
	 * @return string
	 */
	public static function getTitle()
	{
		return "Highrise Integration";
	}


	/**
	 * Get the readable description for this plugin
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return "Look up user profile data in <a href=\"http://highrisehq.com/\">highrise</a> and display this information in fields on ticket and profile views.";
	}
}