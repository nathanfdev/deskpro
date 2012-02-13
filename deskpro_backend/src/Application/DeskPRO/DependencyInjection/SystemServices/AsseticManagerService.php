<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

use Application\DeskPRO\App;
use Assetic\AssetManager;
use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;


class AsseticManagerService
{
	public static function create(DeskproContainer $container, array $options = array())
	{
		$manager = new \Application\DeskPRO\Assetic\AsseticManager(
			App::getConfigFromFile('assets'),
			realpath(DP_ROOT . '/../static'),
			'build'
		);

		if ($container->isScopeActive('request')) {
			$manager->setAssetHelper($container->get('templating.helper.assets'));
		}

		return $manager;
	}
}
