<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\ApiKeys\ApiKeys;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ApiKeysService
{
    public static function create(DeskproContainer $container)
    {
        $x = new ApiKeys($container->getEm());

        return $x;
    }
}
