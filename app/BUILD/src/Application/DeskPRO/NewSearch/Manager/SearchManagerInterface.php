<?php

namespace Application\DeskPRO\NewSearch\Manager;

/**
 * Search Manager Interface.
 */
interface SearchManagerInterface
{
    /**
     * @param null $query
     * @param null $sort
     * @param array $limitTypes
     * @return mixed
     */
    public function quickSearch($query = null, $sort = null, array $limitTypes = []);
}
