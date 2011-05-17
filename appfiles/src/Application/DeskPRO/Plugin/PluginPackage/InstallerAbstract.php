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
abstract class InstallerAbstract
{
	/**
	 * @var string
	 */
	protected $plugin_package_name;
	
	/**
	 * @var array
	 */
	protected $insert_settings = array();

	/**
	 * @var array
	 */
	protected $installer_data = array();

	/**
	 * The install controller
	 */
	protected $controller;

	/**
	 * @var int
	 */
	protected $steps = null;

	
	public final function __construct($plugin_package_name, $controller)
	{
		$this->plugin_package_name = $plugin_package_name;
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
	 * Route: admin_plugins_install
	 * Params: id: this_plugins_id, step: the_step
	 *
	 * Templates should extend: AdminBundle:Plugins:install_step.html.twig
	 * And overwrite the block 'step_content'
	 */
	public function runStep($step)
	{
		$session = $this->controller->session;

		$session_key = $this->plugin_package_name::getName() . '_install';
		if (isset($session[$session_key])) {
			$session_data = $session[$session_key];
			if (!empty($session_data['insert_settings'])) {
				$this->insert_settings = $session_data['insert_settings'];
			}
			if (!empty($session_data['installer_data'])) {
				$this->installer_data = $session_data['installer_data'];
			}
		}

		if (!$this->steps OR $step > $this->steps) {

			App::getOrm()->beginTransaction();
			$plugin = $this->doInstall();

			$step_method = 'stepInstall';
			unset($session[$session_key]);

			$ret = $this->$step_method($plugin);

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
	 * Performs the install by inserting the plugin record, settings,
	 * and listeners.
	 */
	protected function doInstall()
	{
		foreach ($this->insert_settings as $k => $v) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting($k, $v);
		}

		$plugin = \Application\DeskPRO\Entity\Plugin();
		$plugin['id']                     = $this->plugin_package_name::getName();
		$plugin['title']                  = $this->plugin_package_name::getTitle();
		$plugin['description']            = $this->plugin_package_name::getDescription();
		$plugin['version']                = $this->plugin_package_name::getVersion();
		$plugin['package_class']          = $this->plugin_package_name;
		$plugin['package_class_file']     = Util::getClassFilename($this->plugin_package_name);
		$plugin['resources_path']         = $this->plugin_package_name::getResourcesPath();
		$plugin['autoload_paths']         = $this->plugin_package_name::getAutoloadPaths();

		App::getOrm()->persist($plugin);
		App::getOrm()->flush();

		$this->postInstall($plugin);

		$plugin_listeners = $this->getPluginListeners();
		foreach ($plugin_listeners as $plugin_listener) {
			if (is_array($plugin_listener)) {
				$plugin_listener = new \Application\DeskPRO\Entity\PluginListener();
				$plugin_listener->fromArray($plugin_listener);
			}
			$plugin->addPluginListener($plugin_listener);
		}

		App::getOrm()->persist($plugin);
		App::getOrm()->flush();

		return $plugin;
	}

	public function postInstall($plugin) { }



	/**
	 * After the plugin is installed, this method is finally run with the inserted
	 * $plugin. You can do more work here, just return a response (ie confirmation/success page)
	 */
	public function stepInstall($plugin)
	{
		return $this->controller->render('AdminBundle:Plugin:install_done.html.twig', array(
			'title' => $this->plugin_package_name::getTitle()
		));
	}


	/**
	 * Get an array of plugin listeners
	 *
	 * @var array
	 */
	protected function getPluginListeners()
	{
		return array();
	}


	
	/**
	 * Should be called from init() to set the number of steps the installer has.
	 * 0 or the default (null) steps means jumping right to the end.
	 */
	protected function setSteps($steps)
	{
		if ($this->steps !== null) {
			throw new \InvalidMethodException("Steps has already been set");
		}

		$this->steps = (int)$steps;
	}

	
	/**
	 * Insert a user-defind setting
	 * 
	 * @param  $name
	 * @param  $value
	 */
	public function insertUserSetting($name, $value)
	{
		$this->insert_settings[$name] = $value;
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