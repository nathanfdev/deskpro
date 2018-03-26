<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\UserRules\UserRules;

class UserRulesService
{
    public static function create(DeskproContainer $container)
    {
        $x = new UserRules($container->getEm());

        return $x;
    }
}
