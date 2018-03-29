<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Search\SearchIndexer;

class SearchIndexerService
{
    public static function create(DeskproContainer $container)
    {
        $ind = new SearchIndexer($container);

        return $ind;
    }
}
