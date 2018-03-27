<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Exception\NotImplementedException;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class FilterUserField extends AbstractFilterTerm
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
        return 'FilterUserField'.$this->getTermOptions()->get('field_id');
    }
}
