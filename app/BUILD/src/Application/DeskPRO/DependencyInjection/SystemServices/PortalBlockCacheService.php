<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class PortalBlockCacheService
{
    public static function create(DeskproContainer $container)
    {
        $cache = new \Orb\Doctrine\Common\Cache\PreloadedMysqlCache($container->getDb());
        $cache->setPrefix('d.portal.block.');

        return $cache;
    }
}
