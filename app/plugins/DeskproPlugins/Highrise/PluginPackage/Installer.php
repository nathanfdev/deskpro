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

namespace DeskproPlugins\Highrise\PluginPackage;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;
use Orb\Util\Util;

use Application\DeskPRO\Plugin\PluginPackage\InstallerAbstract;

/**
 * A generic installer/uninstaller class that you can extend and use from the PluginPackage
 * to help install the plugin.
 */
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
			$this->insertUserSetting('dp_highrise.api_auth_key', $this->in->getBool('api_auth_key'));
			$this->insertUserSetting('dp_highrise.highrise_url', $this->in->getBool('highrise_url'));
			return $this->controller->redirectRoute('admin_plugins_install_step', array('plugin_id' => 'dp_highrise', 'step' => 99));
		}

		return $this->controller->render('dp_highrise:Install:install_step_1.html.twig');
	}

	public function stepInstall($plugin)
	{
		return $this->controller->render('dp_highrise:Install:install_done.html.twig');
	}

	public function postInstall($plugin)
	{
		// This is the special display field we'll use to inject the results into the viewticket form
		$this->inserted_display_field = new \Application\DeskPRO\Entity\CustomDefTicket();
		$this->inserted_display_field->fromArray(array(
			'plugin' => $plugin,
			'title' => 'Highrise User Information',
			'handler_class' => 'Application\\DeskPRO\\CustomFields\\Handler\\Display',
		));

		App::getOrm()->persist($this->inserted_display_field);
		App::getOrm()->flush();
	}

	/**
	 * Get an array of plugin listeners
	 *
	 * @var array
	 */
	public function getPluginListeners()
	{
		return array(
			array(
				'event_name' => 'DeskPRO_onDisplayFieldRenderHtml',
				'event_options' => array(
					'field_table' => 'custom_def_tickets',
					'field_id'    => $this->inserted_display_field['id']
				),
				'listener_class' => 'DeskproPlugins\\Highrise\\ListenerHandler\\FetchHighriseData'
			)
		);
	}
}