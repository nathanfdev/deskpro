<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Stops the trigger loop by setting the `mute_agent_emails` flag on the context.
 */
class ModMuteAgentEmails extends AbstractAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getVars()->set('mute_agent_emails', true);
    }
}
