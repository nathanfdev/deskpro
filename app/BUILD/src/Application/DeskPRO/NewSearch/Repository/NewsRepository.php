<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * News Repository.
 */
class NewsRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
