<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

interface ActionInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context);
}
