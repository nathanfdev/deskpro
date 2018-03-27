<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\People\AgentPermissions\PermissionNamesLoader;

class AgentPermissionNamesLoaderService
{
    public static function create(DeskproContainer $container)
    {
        $loader = new PermissionNamesLoader();

        return $loader;
    }
}
