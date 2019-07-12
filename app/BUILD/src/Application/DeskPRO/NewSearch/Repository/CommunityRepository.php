<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Community Repository.
 */
class CommunityRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
