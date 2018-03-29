<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\App\AppManipulator;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AppManipulatorService
{
    public static function create(DeskproContainer $container)
    {
        $app_manipulator = new AppManipulator(
            $container->getSystemService('app_manager'), $container->getEm(), $container
        );

        return $app_manipulator;
    }
}
