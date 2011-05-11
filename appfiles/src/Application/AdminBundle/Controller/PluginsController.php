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

class PluginController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	/**
	 * List installed plugins and available plugin
	 */
	public function indexAction()
	{
		$this->rememberLastPage();

		$finder = new PluginFinder();
		$available_plguins = $finder->findPlugins();

		return $this->render('AdminBundle:Plugins:list.html.twig', array(
			'available_plguins' => $available_plguins
		));
	}


	############################################################################
	# install
	############################################################################

	/**
	 * Installs a plugin
	 */
	public function installAction($plugin_id)
	{
		$this->rememberLastPage();

		$finder = new PluginFinder();
		$plugin_info = $finder->getPluginInfo($plugin_id);
		$plugin_package_class = $plugin_info['class'];

		App::getOrm()->beginTransaction();

		$plugin = new Plugin();
		$plugin['id']                 = $plugin_info['name'];
		$plugin['title']              = $plugin_info['title'];
		$plugin['description']        = $plugin_info['description'];
		$plugin['package_class']      = $plugin_info['class'];
		$plugin['package_class_file'] = $plugin_info['class_file'];
		$plugin['version']            = $plugin_info['version'];
		$plugin['autoload_paths']     = $plugin_package_class::getAutoloadPaths();

		foreach ($plugin_package_class::getPluginListeners() as $plugin_listener) {
			$plugin->addPluginListener($plugin_listener);
		}

		$plugin_package_class::install($plugin);

		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->redirectRoute('admin_plugins');
	}


	############################################################################
	# uninstall
	############################################################################

	/**
	 * Installs a plugin
	 */
	public function uninstallAction($plugin_id)
	{
		$this->rememberLastPage();

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