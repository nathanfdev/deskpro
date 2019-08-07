<?php

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\EntityRepository\Traits\ClearSlugHistoryTrait;

/**
 * Class CommunityTopicSlugHistory.
 */
class CommunityTopicSlugHistory extends AbstractEntityRepository
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
