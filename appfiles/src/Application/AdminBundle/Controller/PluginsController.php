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
	public function installAction($plugin_id, $step = 1)
	{
		$this->rememberLastPage();

		$finder = new PluginFinder();
		$plugin_info = $finder->getPluginInfo($plugin_id);
		$plugin_package_class = $plugin_info['class'];

		$installer = $plugin_package_class::getInstaller($this);
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