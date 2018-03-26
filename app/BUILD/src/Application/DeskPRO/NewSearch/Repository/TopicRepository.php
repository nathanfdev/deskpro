<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Topic Repository.
 */
class TopicRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
