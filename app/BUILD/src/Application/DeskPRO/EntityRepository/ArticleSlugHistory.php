<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Traits\ClearSlugHistoryTrait;

/**
 * Class ArticleSlugHistory
 *
 * @package Application\DeskPRO\EntityRepository
 */
class ArticleSlugHistory extends AbstractEntityRepository
{
    use ClearSlugHistoryTrait;

    /**
     * @return string
     */
    protected function getEntityFieldName()
    {
        return 'article_id';
    }
}
