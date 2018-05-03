<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Banning\IpBans;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class IpBansService
{
    public static function create(DeskproContainer $container)
    {
        $x = new IpBans($container->getEm());

        return $x;
    }
}
