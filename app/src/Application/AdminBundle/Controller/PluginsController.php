<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
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

		$installed_plugins_ids = $this->db->fetchAllCol("SELECT id FROM plugins");
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

		$this->container->get('deskpro.plugin_manager')->addPlugin($plugin);

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
		$plugin = $this->em->find('DeskPRO:Plugin', $plugin_id);
		$package_class = $plugin['package_class'];

		$this->em->beginTransaction();

		$this->em->remove($plugin);
		$package_class::uninstall($plugin);

		$this->em->flush();
		$this->em->commit();

		return $this->redirectRoute('admin_plugins');
	}
}
