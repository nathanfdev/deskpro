<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketChangeTracker;

/**
 * Sets/removes on hold status.
 */
class HoldAction extends AbstractAction implements PermissionableAction
{
    /**
     * @var bool
     */
    protected $is_hold;

    /**
     * @var \Application\DeskPRO\Tickets\TicketChangeTracker
     */
    protected $tracker;

    public function __construct($is_hold, TicketChangeTracker $tracker = null)
    {
        $this->is_hold = (bool) $is_hold;
        $this->tracker = $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        // No change, sure they can apply no change
        if ($ticket->is_hold == $this->is_hold) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'set_hold')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket->is_hold = $this->is_hold;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket->is_hold == $this->is_hold) {
            return [];
        }

        return [
            ['action' => 'hold', 'is_hold' => $this->is_hold],
        ];
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
        if ($this->is_hold) {
            return 'Put ticket on hold';
        } else {
            return 'Remove ticket from hold';
        }
    }
}
