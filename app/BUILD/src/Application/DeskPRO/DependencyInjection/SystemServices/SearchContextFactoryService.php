<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\NewSearch\SearchEngine;

class SearchContextFactoryService
{
    public static function create(DeskproContainer $container)
    {
        return new SearchEngine\SearchContextFactory($container);
    }
}
