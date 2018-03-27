<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Feedback Repository.
 */
class FeedbackRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
