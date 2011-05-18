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

namespace DeskproPlugins\Gravatar\PluginPackage;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;
use Orb\Util\Util;

use Application\DeskPRO\Plugin\PluginPackage\InstallerAbstract;

class Installer extends InstallerAbstract
{
	public function postInstall($plugin)
	{
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_gravatar', 1);
	}
}