<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\People\EmailAddressValidator;

class EmailAddressValidatorService
{
    public static function create(DeskproContainer $container)
    {
        $v = new EmailAddressValidator(
            $container->getEmailAccountManager(),
            $container->getEm()->getRepository('DeskPRO:BanEmail')
        );

        return $v;
    }
}
