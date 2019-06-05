<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Traits\ClearSlugHistoryTrait;

/**
 * Class NewsSlugHistory
 *
 * @package Application\DeskPRO\EntityRepository
 */
class NewsSlugHistory extends AbstractEntityRepository
{
    use ClearSlugHistoryTrait;

    protected function getEntityFieldName()
    {
        return 'news_id';
    }
}
