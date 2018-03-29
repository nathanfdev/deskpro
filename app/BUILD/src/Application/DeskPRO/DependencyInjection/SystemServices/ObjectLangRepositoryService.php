<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Translate\ObjectLangRepository;

class ObjectLangRepositoryService
{
    public static function create(DeskproContainer $container)
    {
        $olr = new ObjectLangRepository($container->getEm());

        return $olr;
    }
}
