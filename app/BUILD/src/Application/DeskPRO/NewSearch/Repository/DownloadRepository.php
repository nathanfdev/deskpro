<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Download Repository.
 */
class DownloadRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];
}
