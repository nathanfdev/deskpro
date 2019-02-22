<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class SetActionTimes implements TicketSaveActionInterface
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        $state = $ticket->getStateChangeRecorder();

        if ($state->hasNewUserReply()) {
            $ticket->date_last_user_reply = new \DateTime();
        }
        if ($state->hasNewAgentReply()) {
            $ticket->date_last_agent_reply = new \DateTime();

            if (!$ticket->date_first_agent_reply) {
                $ticket->date_first_agent_reply = new \DateTime();
                $ticket->total_to_first_reply   = $ticket->date_first_agent_reply->getTimestamp() - $ticket->date_created->getTimestamp();
            }
        }
        if ($state->hasChangedField('status') || $state->hasChangedField('ticket_status')) {
            $ticket->date_status = new \DateTime();
        }
    }
}
