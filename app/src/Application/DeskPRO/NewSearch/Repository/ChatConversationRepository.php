<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Person Repository
 */
class ChatConversationRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * @return array
     */
    protected function getQueryFields()
    {
        return array('_all');
    }

    /**
     * Constructs the filters array to handle agent permission
     *
     * @return array
     */
    protected function getFilters()
    {
        return array('term' => array('is_agent' => false));
    }
}
