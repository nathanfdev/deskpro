<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Feedback Repository
 */
class FeedbackRepository extends AbstractRepository
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
