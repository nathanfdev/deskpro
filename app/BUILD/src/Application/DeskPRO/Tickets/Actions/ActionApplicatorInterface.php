<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

interface ActionApplicatorInterface
{
    /**
     * @param ActionInterface          $action
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function apply(ActionInterface $action, Ticket $ticket, ExecutorContextInterface $context);
}
