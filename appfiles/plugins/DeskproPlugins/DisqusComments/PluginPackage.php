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

namespace DeskproPlugins\FacebookWidgets;

use Application\DeskPRO\Entity\Plugin;
use Application\DeskPRO\Plugin\PluginPackage\AbstractPluginPackage;
use DeskproPlugins\DisqusComments\PluginPackage\Installer;
use DeskproPlugins\DisqusComments\PluginPackage\Uninstaller;

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
		$installer = new Installer('DeskproPlugins\\DisqusComments\\PluginPackage', $install_controller);
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
		return 'dp_disqus';
	}


	/**
	 * Get the readable title for this plugin
	 *
	 * @return string
	 */
	public static function getTitle()
	{
		return "Disqus Comments";
	}


	/**
	 * Get the readable description for this plugin
	 *
	 * @return string
	 */
	public static function getDescription()
	{
		return "Replace default commenting in articles with Disqus";
	}
}