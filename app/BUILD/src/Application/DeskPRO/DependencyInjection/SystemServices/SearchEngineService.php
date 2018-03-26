<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\NewSearch\SearchEngine;

class SearchEngineService
{
    public static function create(DeskproContainer $container)
    {
        $search = new SearchEngine\UserSearchProxy($container);
        $se     = new SearchEngine\SearchEngine($search);

        return $se;
    }
}
