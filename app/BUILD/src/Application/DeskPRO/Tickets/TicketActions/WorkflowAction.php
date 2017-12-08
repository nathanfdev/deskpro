<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class WorkflowAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $workflow_id;

    public function __construct($workflow)
    {
        $this->workflow_id = $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['workflow_id'] = $this->workflow_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['workflow_id'] == $this->workflow_id) {
            return [];
        }

        return [
            ['action' => 'workflow', 'workflow_id' => $this->workflow_id],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getWorkflowId() == $this->workflow_id) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return false;
        }

        return true;
    }

    /**
     * Get the workflow id.
     *
     * @return int
     */
    public function getWorkflowId()
    {
        return $this->workflow_id;
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

        if ($this->workflow_id == 0) {
            return $tr->phrase('agent.tickets.remove_workflow_action');
        } else {
            $names = App::getEntityRepository('DeskPRO:TicketWorkflow')->getNames();
            if (!isset($names[$this->workflow_id])) {
                $name = "<error>Unknown #{$this->workflow_id}</error>";
            } else {
                $name = $as_html ? htmlspecialchars($names[$this->workflow_id]) : $names[$this->workflow_id];
            }

            return $tr->phrase('agent.tickets.set_workflow_action', ['workflow' => $name]);
        }
    }
}
