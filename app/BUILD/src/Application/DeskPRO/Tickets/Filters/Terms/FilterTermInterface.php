<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * This interface is used when a TicketCriteria class can be used with filters and escalations.
 * Filter criteria is used to build up raw SQL queries.
 */
interface FilterTermInterface
{
    /**
     * @return FilterQuery|null
     */
    public function getFilterQuery(ExecutorContextInterface $context = null);
}
