<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Exception\NotImplementedException;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class FilterTicketField extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        throw new NotImplementedException();
    }

    /**
     * @return string
     */
    public function getTermType()
    {
        return 'FilterTicketField'.$this->getTermOptions()->get('field_id');
    }
}
