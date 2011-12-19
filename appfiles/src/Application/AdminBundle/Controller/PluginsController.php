<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Plugin\PluginFinder;
use Application\DeskPRO\Entity\Plugin;

class PluginsController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	/**
	 * List installed plugins and available plugin
	 */
	public function listAction()
	{
		$finder = new PluginFinder();
		$available_plguins = $finder->findPlugins();

		$installed_plugins_ids = App::getDb()->fetchAllCol("SELECT id FROM plugins");
		$installed_plugins = array();

		foreach ($installed_plugins_ids as $plugin_id) {
			$installed_plugins[$plugin_id] = $available_plguins[$plugin_id];
			unset($available_plguins[$plugin_id]);
		}

		return $this->render('AdminBundle:Plugins:list.html.twig', array(
			'available_plguins' => $available_plguins,
			'installed_plugins' => $installed_plugins
		));
	}


	############################################################################
	# install
	############################################################################

	/**
	 * Installs a plugin
	 */
	public function installAction($plugin_id, $step = 1)
	{
		$finder = new PluginFinder();
		$plugin_info = $finder->getPluginInfo($plugin_id);
		$package_name = $plugin_info['class'];

		$plugin = new \Application\DeskPRO\Entity\Plugin();
		$plugin['id']                     = $package_name::getName();
		$plugin['title']                  = $package_name::getTitle();
		$plugin['description']            = $package_name::getDescription();
		$plugin['version']                = $package_name::getVersion();
		$plugin['package_class']          = $package_name;
		$plugin['package_class_file']     = $plugin_info['class_file'];
		$plugin['resources_path']         = $package_name::getResourcesPath();
		$plugin['autoload_paths']         = $package_name::getAutoloadPaths();

		App::get('deskpro.plugin_manager')->addPlugin($plugin);

		$autoload_paths = $package_name::getAutoloadPaths();
		if ($autoload_paths) {
			array_walk($autoload_paths, function(&$path) {
				$path = DP_ROOT . '/plugins' . $path;
			});
			App::getClassLoader()->registerNamespaces($autoload_paths);
		}

		$installer = $package_name::getInstaller($this, $plugin);

		return $installer->runStep($step);
	}


	############################################################################
	# uninstall
	############################################################################

	/**
	 * Installs a plugin
	 */
	public function uninstallAction($plugin_id)
	{
		$plugin = App::findEntity('DeskPRO:Plugin', $plugin_id);
		$package_class = $plugin['package_class'];

		App::getOrm()->beginTransaction();

		App::getOrm()->remove($plugin);
		$package_class::uninstall($plugin);

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->redirectRoute('admin_plugins');
	}
}
