<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Traits\ClearSlugHistoryTrait;

/**
 * Class DownloadSlugHistory
 *
 * @package Application\DeskPRO\EntityRepository
 */
class DownloadSlugHistory extends AbstractEntityRepository
{
    use ClearSlugHistoryTrait;

    /**
     * @return string
     */
    protected function getEntityFieldName()
    {
        return 'download_id';
    }
}
