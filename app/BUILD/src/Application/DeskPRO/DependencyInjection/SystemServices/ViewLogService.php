<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class ViewLogService
{
    public static function create(DeskproContainer $container)
    {
        $view_log = new \Application\DeskPRO\Log\ViewLog(
            $container->getDb(),
            $container->getSession()
        );

        return $view_log;
    }
}
