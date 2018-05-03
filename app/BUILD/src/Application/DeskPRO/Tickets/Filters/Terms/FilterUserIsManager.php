<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Filters based on ticket user manager status.
 */
class FilterUserIsManager extends AbstractFilterTerm
{
    /**
     * {@inheritdoc}
     */
    public function getFilterQuery(ExecutorContextInterface $context = null)
    {
        $query = new FilterQuery();
        switch ($this->getTermOperator()) {
            case self::OP_IS:
                $query->andWhere('user.organization_manager = 1');
            case self::OP_NOT:
                $query->andWhere('user.organization_manager = 0');
                break;
            default:
                throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
        }

        $query->addJoin('tickets.person', 'people', 'user', 'user.person_id = tickets.person_id');

        return $query;
    }
}
