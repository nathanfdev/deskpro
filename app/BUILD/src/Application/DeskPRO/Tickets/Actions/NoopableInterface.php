<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

interface NoopableInterface
{
    /**
     * Check if the current action wolud result in a no-op.
     *
     * For example, if the action is set to change the department to ID 5 and
     * the ticket is already in department 5, then it would be a no-op.
     *
     * We skip no-op actions.
     *
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return bool
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context);
}
