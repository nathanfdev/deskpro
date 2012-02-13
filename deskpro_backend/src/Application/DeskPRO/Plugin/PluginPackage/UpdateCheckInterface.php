<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Addon\Installer;

use Application\DeskPRO\Entity\Plugin;

/**
 * Add this interface to the PluginPackage and it'll gain auto-update checking capabilities.
 */
interface UpdateCheckInterface
{
	/**
	 * Checks for a new version and returns it if there is one.
	 *
	 * @param  \Application\DeskPRO\Entity\Plugin $plugin Current plugin (ie use $plugin['version'])
	 * @return string|null
	 */
	public function checkNewVersion(Plugin $plugin);
}