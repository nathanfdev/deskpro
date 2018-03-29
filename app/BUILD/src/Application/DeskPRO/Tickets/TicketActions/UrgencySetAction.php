<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

/**
 * Sets the ticket urgency to a specifc value.
 */
class UrgencySetAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $num;
    /** @var bool|null */
    protected $allow_lower;

    public function __construct($num, $allow_lower = null)
    {
        $this->num         = $num;
        $this->allow_lower = $allow_lower;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        if ($this->allow_lower || $ticket->urgency < $this->num) {
            $ticket['urgency'] = $this->num;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['urgency'] == $this->num) {
            return [];
        }

        return [
            ['action' => 'urgency', 'urgency' => $this->num],
        ];
    }

    /**
     * Get the number modifier.
     *
     * @return int
     */
    public function getNum()
    {
        return $this->num;
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

        if ($this->allow_lower) {
            return $tr->phrase('admin.tickets.set_urgency_to_x', ['urgency' => $this->num]);
        } else {
            return $tr->phrase('admin.tickets.set_urgency_to_x_when_lower', ['urgency' => $this->num]);
        }
    }
}
