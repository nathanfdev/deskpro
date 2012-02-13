<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ResourceScanner;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

class SettingFiles
{
	public function getAllSettings()
	{
		$check_dirs = array(
			DP_ROOT.'/src/Application/AdminBundle/Resources/settings',
			DP_ROOT.'/src/Application/AgentBundle/Resources/settings',
			DP_ROOT.'/src/Application/ApiBundle/Resources/settings',
			DP_ROOT.'/src/Application/DeskPRO/Resources/settings',
			DP_ROOT.'/src/Application/DevBundle/Resources/settings',
			DP_ROOT.'/src/Application/UserBundle/Resources/settings',
		);

		$check_dirs = array_filter($check_dirs, function($v) {
			return is_dir($v);
		});

		$finder = new \Symfony\Component\Finder\Finder();
		$finder->files()->name('*.php')->in($check_dirs);

		$names = array();

		foreach ($finder as $file) {
			$settings = require($file->getPathname());
			$names = array_merge($names, $settings);
		}

		return $names;
	}
}
