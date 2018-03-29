<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;

interface UserSearchInterface
{
    /**
     * @param SearchContextInterface $context
     * @param string                 $query
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function search(SearchContextInterface $context, $query, array $options = null);

    /**
     * @param SearchContextInterface $context
     * @param string                 $content
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function similarTo(SearchContextInterface $context, $content, array $options = null);
}
