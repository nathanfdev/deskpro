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
 * Stops the trigger loop by setting the `stop_triggers` flag on the context.
 */
class ModStopTriggers extends AbstractAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $context->getVars()->set('stop_triggers', true);
    }
}
