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

namespace DeskproPlugins\DisqusComments\PluginPackage;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;
use Orb\Util\Util;

use Application\DeskPRO\Plugin\PluginPackage\InstallerAbstract;

class Installer extends InstallerAbstract
{
	protected $in;

	protected $inserted_display_field;

	protected function init()
	{
		$this->setSteps(1);
		$this->in = $this->controller->in;
	}

	public function step1()
	{
		if ($this->in->getBool('process')) {
			$this->insertUserSetting('core.disqus_shortname', $this->in->getString('facebook_admins'));
			return $this->controller->redirectRoute('admin_plugins_install_step', array('plugin_id' => 'dp_disqus', 'step' => 99));
		}

		return $this->controller->render('dp_disqus:Install:install_step_1.html.twig');
	}

	public function stepInstall($plugin)
	{
		return $this->controller->render('dp_disqus:Install:install_done.html.twig');
	}

	public function postInstall($plugin)
	{
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.comments_adapter', 'disqus');
	}

	/**
	 * Get an array of plugin listeners
	 *
	 * @var array
	 */
	public function getPluginListeners()
	{
		return array();
	}
}