<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory;

class UsersourceAuthAdapterFactoryService
{
    /**
     * @param DeskproContainer $container
     * @param array            $options
     *
     * @return \Application\DeskPRO\Sms\DeskPROSmsSender
     */
    public static function create(DeskproContainer $container, $options = [])
    {
        return new UsersourceAuthAdapterFactory(
            $container, $container->getRouter(), $container->getSession(), isset($options['interface']) ? $options['interface'] : DP_INTERFACE
        );
    }
}
