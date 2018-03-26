<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\People\PasswordPolicyValidator;
use Application\DeskPRO\Settings\PasswordSettings;

class PasswordPolicyValidatorService
{
    public static function create(DeskproContainer $container)
    {
        $settings          = $container->getSettingsHandler();
        $password_settings = new PasswordSettings($settings);

        return new PasswordPolicyValidator(
            $password_settings->getUserPolicy(),
            $password_settings->getAgentPolicy(),
            $container->getEm()->getRepository('DeskPRO:PasswordHistory'),
            $container->get('session')
        );
    }
}
