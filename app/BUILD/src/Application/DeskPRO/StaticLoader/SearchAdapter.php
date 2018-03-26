<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\StaticLoader;

/**
 * This static loader is used with the DI to create a search adapter.
 *
 * @see \Application\DeskPRO\DependencyInjection\SearchExtension
 */
class SearchAdapter
{
    public static function getSearchAdapter()
    {
        if (!$adapter = new \Application\DeskPRO\Search\Adapter\MysqlAdapter()) {
            throw new \Exception('No search adapter');
        }

        return $adapter;
    }
}
