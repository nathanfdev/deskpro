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

use Application\DeskPRO\Entity\Plugin;
use Application\DeskPRO\Plugin\PluginPackage\AbstractPluginPackage;
use DeskproPlugins\Highrise\PluginPackage\Installer;
use DeskproPlugins\Highrise\PluginPackage\Uninstaller;

class PluginPackage extends AbstractPluginPackage
{
	/**
	 * Called the first time the plugin is installed.
	 *
	 * @return InstallerAbstract
	 */
	public static function getInstaller($install_controller, Plugin $plugin)
	{
		$installer = new Installer($plugin, $install_controller);
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
		$installer = new Installer('DeskproPlugins\\Highrise\\PluginPackage', $install_controller);
		return $install_controller;
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