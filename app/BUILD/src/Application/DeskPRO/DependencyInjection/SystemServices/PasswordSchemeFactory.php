<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class PasswordSchemeFactory
{
    public static function create(DeskproContainer $container, $options = [])
    {
        if (empty($options['scheme'])) {
            throw new \InvalidArgumentException('Must have the `scheme` option set');
        }

        switch ($options['scheme']) {
            case 'deskpro3':
            case 'deskpro3_tech':
                return new \Application\DeskPRO\People\PasswordScheme\Deskpro3();

            case 'deskpro4original':
                return new \Application\DeskPRO\People\PasswordScheme\Deskpro4Original();

            case 'bcrypt':
                return new \Application\DeskPRO\People\PasswordScheme\Bcrypt();

            default:
                throw new \InvalidArgumentException("Unknown password scheme `{$options['scheme']}`");
        }
    }
}
