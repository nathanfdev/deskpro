<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class DepartmentAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $department_id;

    public function __construct($department)
    {
        $this->department_id = $department;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getDepartmentId() == $this->department_id) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'department')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $dep_id = $this->department_id;

        // legacy option is noop
        if ($dep_id == 'email_account') {
            return;
        }

        $ticket['department_id'] = $dep_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['department_id'] == $this->department_id) {
            return [];
        }

        return [
            ['action' => 'department', 'department_id' => $this->department_id],
        ];
    }

    /**
     * Get the department id.
     *
     * @return int
     */
    public function getDepartmentId()
    {
        return $this->department_id;
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
        if ($this->department_id == 'email_account') {
            return 'Set department as the linked department for the email account';
        }

        $tr = App::getTranslator();

        $names = App::getDataService('Department')->getFullNames();
        if (!isset($names[$this->department_id])) {
            $name = "<error>Unknown #{$this->department_id}</error>";
        } else {
            $name = $names[$this->department_id];
        }

        return '<span class="with-department" data-department-id="'.$this->department_id.'">'
            .$tr->phrase('agent.tickets.set_department_action', ['department' => $name])
            .'</span>'
            ;
    }
}
