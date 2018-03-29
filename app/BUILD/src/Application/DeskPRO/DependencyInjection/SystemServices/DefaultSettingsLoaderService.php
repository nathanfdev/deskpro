<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\NewSettings\Loader\ConfigPhpFileLoader;

class DefaultSettingsLoaderService
{
    public static function create(DeskproContainer $container)
    {
        $simple_array_cache = $container->get('cache.simple_array');

        // the loader that represents the "default" values in the system
        return new ConfigPhpFileLoader(DP_ROOT.'/sys/config/settings.php', $simple_array_cache);
    }
}
