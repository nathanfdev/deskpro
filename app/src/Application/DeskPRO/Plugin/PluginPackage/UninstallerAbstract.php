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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;
use Orb\Util\Util;

/**
 * A generic installer/uninstaller class that you can extend and use from the PluginPackage
 * to help install the plugin.
 */
abstract class UninstallerAbstract
{
	/**
	 * @var string
	 */
	protected $plugin_package_name;

	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The install controller
	 */
	protected $controller;

	/**
	 * @var int
	 */
	protected $steps = null;


	public final function __construct($plugin, $controller)
	{
		$this->plugin_package_name = $plugin['package_class'];
		$this->plugin = $plugin;
		$this->controller = $controller;
		$this->init();
	}

	/**
	 * Override ths method to run your own init code
	 */
	protected function init() {}


	/**
	 * This executes stepX methods on this object, and must return a response as if
	 * this were a real controller. Use $controller to do controller things.
	 *
	 * Route: admin_plugins_uninstall
	 * Params: id: this_plugins_id, step: the_step
	 *
	 * Templates should extend: AdminBundle:Plugins:uninstall_step.html.twig
	 * And overwrite the block 'step_content'
	 */
	public function runStep($step)
	{
		$session = $this->controller->session;

		$session_key = $this->plugin_package_name::getName() . '_uninstall';
		if (isset($session[$session_key])) {
			$session_data = $session[$session_key];
			if (!empty($session_data['installer_data'])) {
				$this->installer_data = $session_data['installer_data'];
			}
		}

		if (!$this->steps OR $step > $this->steps) {

			App::getOrm()->beginTransaction();
			$this->preUninstall();
			$this->doUninstall();

			$step_method = 'stepInstall';
			unset($session[$session_key]);

			$ret = $this->$step_method();

			App::getOrm()->commit();
		} else {
			$step_method = 'step' . $step;
			$ret = $this->$step_method();
			$session[$session_key] = array(
				'insert_settings' => $this->insert_settings,
				'installer_data' => $this->installer_data
			);
		}

		return $ret;
	}

	
	/**
	 * Empty hook to cleanup any non-standard items
	 */
	protected function preUninstall() { }


	/**
	 * Performs the install by inserting the plugin record, settings,
	 * and listeners.
	 */
	protected function doUninstall()
	{
		App::getDb()->executeUpdate("
			DELETE FROM settings WHERE groupname LIKE '".$this->plugin['id']."%'
		");

		App::getDb()->executeUpdate("
			DELETE FROM templates WHERE path LIKE '".$this->plugin['id'].":%'
		");

		App::getDb()->executeUpdate("
			DELETE FROM plugin_listeners WHERE plugin_id = '".$this->plugin['id']."'
		");

		App::getOrm()->remove($plugin);
		App::getOrm()->flush();
	}



	/**
	 * After the plugin is uninstalled
	 */
	public function stepUninstall()
	{
		return $this->controller->render('AdminBundle:Plugin:uninstall_done.html.twig', array(
			'title' => $this->plugin_package_name::getTitle()
		));
	}


	/**
	 * Insert an installer preference that is saved between page loads.
	 *
	 * @param  $name
	 * @param  $value
	 * @return void
	 */
	public function setInstallerPref($name, $value)
	{
		$this->installer_data[$name] = $value;
	}


	/**
	 * Get the value of an installer pref
	 *
	 * @param  $name
	 * @param $default
	 * @return array|null
	 */
	public function getInstalerPref($name, $default = null)
	{
		return isset($this->installer_data[$name]) ? $this->installer_data[$name] : $default;
	}
}