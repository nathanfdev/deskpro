<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * This interface is used when a TicketCriteria class can respond to triggers.
 * Trigger criteria happen in PHP-land and require PHP to compare values.
 */
interface TriggerTermInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return bool
     */
    public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context);
}
