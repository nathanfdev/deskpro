<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\RefGenerator\CustomRef;
use Application\DeskPRO\RefGenerator\RandomRef;

class RefGeneratorService
{
    public static function create(DeskproContainer $container)
    {
        if ($container->getSetting('core.ref_pattern')) {
            $ref_gen = new CustomRef(
                $container->getEm(),
                $container->getSetting('core.ref_pattern'),
                $container->getSetting('core.default_timezone', 'UTC'),
                (int) $container->getSetting('core.ref_append_counter')
            );
        } else {
            $ref_gen = new RandomRef($container->getEm());
        }

        return $ref_gen;
    }
}
