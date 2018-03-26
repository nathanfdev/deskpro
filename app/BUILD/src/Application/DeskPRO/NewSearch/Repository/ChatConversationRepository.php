<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Person Repository.
 */
class ChatConversationRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getFilters(array $options = [])
    {
        return ['term' => ['is_agent' => false]];
    }
}
