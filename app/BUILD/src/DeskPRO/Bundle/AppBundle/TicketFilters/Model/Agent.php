<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class Agent
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $primary_team = 0;

    /**
     * @var int[]
     */
    public $teams = [];

    /**
     * @var int[]
     */
    public $allowed_departments = [];

    /**
     * A hint that says that allowed_departments is all departments.
     * This enables some optimisations because we can skip dep checks.
     *
     * @var bool
     */
    public $all_departments_allowed = false;

    /**
     * @var bool
     */
    public $view_unassigned = false;

    /**
     * @var bool
     */
    public $view_assigned = false;

    /**
     * Does the agent have perms to view everything?
     *
     * @return bool
     */
    public function canViewAll()
    {
        return $this->all_departments_allowed && $this->view_assigned && $this->view_unassigned;
    }

    /**
     * @param TicketModel $ticketModel
     *
     * @return bool
     */
    public function canViewTicket(TicketModel $ticketModel)
    {
        if ($this->canViewAll()) {
            return true;
        }

        if ($ticketModel->agent === $this->id) {
            return true;
        }

        if ($ticketModel->agent_team && in_array($ticketModel->agent_team, $this->teams)) {
            return true;
        }

        if ($this->all_departments_allowed || in_array($ticketModel->department, $this->allowed_departments)) {
            if ($this->view_assigned && $ticketModel->agent !== 0) {
                return true;
            }
            if ($this->view_unassigned && $ticketModel->agent === 0) {
                return true;
            }
        }

        return false;
    }
}
