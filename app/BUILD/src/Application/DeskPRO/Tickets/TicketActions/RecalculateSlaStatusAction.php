<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * Recalculate SLA status - this is really a no-op as the SLA processor will do it.
 */
class RecalculateSlaStatusAction extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        // there's nothing to do here - this only shows up when editing an sla trigger
        // and the cron process will handle it. This just means that there's always an action listed.
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        return $tr->phrase('agent.tickets.recalculate_sla_status_action');
    }
}
