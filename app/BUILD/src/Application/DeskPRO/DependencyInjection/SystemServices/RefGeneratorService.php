<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class RefGeneratorService
{
    public static function create(DeskproContainer $container)
    {
        if ($container->getSetting('core.ref_pattern')) {
            $ref_gen = new \Application\DeskPRO\RefGenerator\CustomRef($container->getEm(), $container->getSetting('core.ref_pattern'), (int) $container->getSetting('core.ref_append_counter'));
        } else {
            $ref_gen = new \Application\DeskPRO\RefGenerator\RandomRef($container->getEm());
        }

        return $ref_gen;
    }
}
