<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Banning\EmailBans;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class EmailBansService
{
    public static function create(DeskproContainer $container)
    {
        $x = new EmailBans($container->getEm());

        return $x;
    }
}
