<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Chat\UserChat\UserChatManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Orb\Util\CheckedOptionsArray;

class UserChatManagerFactory
{
    public static function create(DeskproContainer $container, CheckedOptionsArray $options)
    {
        $o = new UserChatManager(
            $options->session,
            $container->get('doctrine.orm.entity_manager'),
            $container->get('deskpro.core.translate'),
            $container->getPersonActivityLogger(),
            $container->get('event_dispatcher')
        );

        return $o;
    }
}
