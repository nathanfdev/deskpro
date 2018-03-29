<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Groups\PermissionsLoader;

class PermissionsLoaderService extends BaseRepositoryService
{
    /**
     * {@inheritdoc}
     */
    public static function create(DeskproContainer $container, array $options = null)
    {
        return new PermissionsLoader($container->getDb());
    }
}
