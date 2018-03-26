<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Takes care of unassigning an agent when the status becomes awaiting_agent and the agent is deleted.
 */
class VerifyAgent implements TicketSaveActionInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        $agent = $ticket->getAgent();

        if ($agent) {
            $context->getLogger()->info('Checking assigned agent is not deleted');

            if (!$agent->isActiveAgent()) {
                $context->getLogger()->info('Unassigning deleted agent');
                $ticket->setAgent(null);
            }
        }
    }
}
