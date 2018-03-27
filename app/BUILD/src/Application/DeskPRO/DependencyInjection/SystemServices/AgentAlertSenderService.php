<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\AgentAlert\AlertSender;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AgentAlertSenderService
{
    public static function create(DeskproContainer $container, array $options = [])
    {
        $alerter = new AlertSender(
            $container->getEm(),
            $container->get('avatar_resolver'),
            $container->get('event_dispatcher')
        );

        return $alerter;
    }
}
