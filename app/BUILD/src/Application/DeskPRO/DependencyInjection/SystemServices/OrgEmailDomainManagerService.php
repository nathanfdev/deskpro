<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Organizations\EmailDomainManager;

class OrgEmailDomainManagerService
{
    public static function create(DeskproContainer $container)
    {
        $email_manager = new EmailDomainManager(
            $container->get('doctrine.orm.entity_manager')
        );

        return $email_manager;
    }
}
