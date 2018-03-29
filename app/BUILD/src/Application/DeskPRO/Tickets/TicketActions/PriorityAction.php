<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class PriorityAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $priority_id;

    public function __construct($priority)
    {
        $this->priority_id = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['priority_id'] = $this->priority_id;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getPriorityId() == $this->priority_id) {
            return true;
        }

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
        if ($ticket['priority_id'] == $this->priority_id) {
            return [];
        }

        return [
            ['action' => 'priority', 'priority_id' => $this->priority_id],
        ];
    }

    /**
     * Get the priority id.
     *
     * @return int
     */
    public function getPriorityId()
    {
        return $this->priority_id;
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

        if ($this->priority_id == 0) {
            return $tr->phrase('agent.tickets.remove_priority_action');
        } else {
            $names = App::getEntityRepository('DeskPRO:TicketPriority')->getNames();
            if (!isset($names[$this->priority_id])) {
                $name = "<error>Unknown #{$this->priority_id}</error>";
            } else {
                $name = $as_html ? htmlspecialchars($names[$this->priority_id]) : $names[$this->priority_id];
            }

            return $tr->phrase('agent.tickets.set_priority_action', ['priority' => $name]);
        }
    }
}
