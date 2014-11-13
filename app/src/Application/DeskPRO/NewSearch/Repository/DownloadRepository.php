<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Download Repository
 */
class DownloadRepository extends AbstractRepository
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
