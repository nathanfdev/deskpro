<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Ticket;

/**
 * Signals that the ticket tab should be closed during a macro call.
 */
class CloseTicketTabAction extends AbstractAction
{
    /** @var \Application\DeskPRO\Tickets\TicketChangeTracker */
    protected $tracker;

    public function __construct(\Application\DeskPRO\Tickets\TicketChangeTracker $tracker = null)
    {
        $this->tracker = $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $GLOBALS['DP_TICKET_CLOSE_TAB'] = true;
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
        return '<span class="with-close-tab">Close ticket tab</span>';
    }
}
