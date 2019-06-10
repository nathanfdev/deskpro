<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Traits\ClearSlugHistoryTrait;

/**
 * Class TopicSlugHistory.
 */
class TopicSlugHistory extends AbstractEntityRepository
{
    use ClearSlugHistoryTrait;

    /**
     * @return string
     */
    protected function getEntityFieldName()
    {
        return 'topic_id';
    }
}
