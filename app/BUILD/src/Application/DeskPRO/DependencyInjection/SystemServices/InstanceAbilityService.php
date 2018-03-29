<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class InstanceAbilityService
{
    public static function create(DeskproContainer $container)
    {
        $o = new \Application\DeskPRO\InstanceAbility();

        return $o;
    }
}
