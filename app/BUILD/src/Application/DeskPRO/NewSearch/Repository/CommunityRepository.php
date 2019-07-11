<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Feedback Repository.
 */
class CommunityRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
