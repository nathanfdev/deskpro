<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

/**
 * Modifies the ticket urgency.
 */
class UrgencyAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $num;

    public function __construct($num)
    {
        $this->num = $num;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['urgency'] = $ticket['urgency'] + $this->num;
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
        return [
            [
                'action'  => 'urgency',
                'urgency' => $ticket['urgency'] + $this->num,
            ],
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
        return new self($this->getNum() + $otherAction->getNum());
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();
        if (!$this->num) {
            return '';
        }

        if ($this->num < 0) {
            return $tr->phrase('agent.tickets.decrease_urgency_action', ['amount' => abs($this->num)]);
        } else {
            return $tr->phrase('agent.tickets.increase_urgency_action', ['amount' => $this->num]);
        }
    }
}
