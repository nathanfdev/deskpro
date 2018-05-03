<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class PublishStructureService
{
    public static function create(DeskproContainer $container)
    {
        $structure = new \Application\DeskPRO\Publish\Structure(
            $container->getSession()->getPerson(),
            $container->getEm(),
            $container->getSystemService('publish_structure_cache')
        );

        return $structure;
    }
}
