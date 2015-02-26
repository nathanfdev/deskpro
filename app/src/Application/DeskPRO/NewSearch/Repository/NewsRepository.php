<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * News Repository
 */
class NewsRepository extends AbstractRepository
{
    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'title' => array('fragment_size' => 100)
    );
}
