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

namespace DeskproPlugins\FacebookWidgets\PluginPackage;

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
			$this->insertUserSetting('core.facebook_like', $this->in->getBool('facebook_like'));
			$this->insertUserSetting('core.facebook_comments_num_posts', $this->in->getUint('facebook_comments_num_posts'));
			$this->insertUserSetting('core.facebook_admins', $this->in->getString('facebook_admins'));

			$this->setInstallerPref('use_fb_comments', $this->in->getBool('use_fb_comments'));

			return $this->controller->redirectRoute('admin_plugins_install_step', array('plugin_id' => 'dp_fbwidgets', 'step' => 99));
		}

		return $this->controller->render('dp_fbwidgets:Install:install_step_1.html.twig');
	}

	public function stepInstall($plugin)
	{
		return $this->controller->render('dp_fbwidgets:Install:install_done.html.twig');
	}

	public function postInstall($plugin)
	{
		if ($this->getInstalerPref('use_fb_comments')) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.comments_adapter', 'facebook');
		}
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