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

class PublishStructureCacheService
{
	public static function create(DeskproContainer $container)
	{
		$cache = new \Orb\Doctrine\Common\Cache\PreloadedMysqlCache($container->getDb());
		$cache->setPrefix('d.publish.structure');

		return $cache;
	}
}
