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
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\TicketChangeTracker;

/**
 * Sets agent.
 */
class AgentAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
    /** @var int */
    protected $agent_id;
    /** @var Person */
    protected $person_context;
    /** @var TicketChangeTracker */
    protected $tracker;

    public function __construct($agent, \Application\DeskPRO\Tickets\TicketChangeTracker $tracker = null)
    {
        $this->agent_id = $agent;
        $this->tracker  = $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getAgentId() == $this->agent_id) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
            if ($this->agent_id == $person->getId() && $person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $agent_id = $this->agent_id;

        if ($agent_id == -1) {
            if ($this->tracker && $this->tracker->isExtraSet('fwd_via_agent')) {
                $agent_id = $this->tracker->getExtra('fwd_via_agent')->getId();
            } else {
                // Invalid context
                if (!$this->person_context or !$this->person_context['is_agent']) {
                    return;
                }

                $agent_id = $this->person_context['id'];
            }
        }

        if ($agent_id == 0) {
            $agent = null;
        } else {
            $agent = App::getDataService('Agent')->get($agent_id);

            if (!$agent) {
                // Agent is invalid, skip
                return;
            }
        }

        $ticket['agent'] = $agent;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        $agent_id = $this->agent_id;

        if ($agent_id == -1) {
            // Invalid context
            if (!$this->person_context or !$this->person_context['is_agent']) {
                return [];
            }

            $agent_id = $this->person_context['id'];
        }

        if ($ticket['agent_id'] == $agent_id) {
            return [];
        }

        return [
            ['action' => 'agent', 'agent_id' => $agent_id],
        ];
    }

    /**
     * Get the agent id.
     *
     * @return int
     */
    public function getAgentId()
    {
        return $this->agent_id;
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

        if ($this->agent_id == -1) {
            if ($this->trigger) {
                switch ($this->trigger->event_trigger) {
                    case 'new.email.agent': return 'Assign the agent who forwarded the email';
                    case 'new.web.agent.portal': return 'Assign the agent who created the ticket';
                    case 'update.agent': return 'Assign the agent who made the change';
                }
            }

            return '<span class="with-agent" data-agent-id="'.$this->agent_id.'">'.$tr->phrase('agent.tickets.assign_current_action').'</span>';
        } elseif ($this->agent_id == 0) {
            if ($this->trigger && $this->trigger->event_trigger == 'new.email.agent') {
                return 'Do not assign ticket to anyone';
            }

            return $tr->phrase('agent.tickets.unassign_action');
        } else {
            $name = App::getEntityRepository('DeskPRO:Person')->getAgentName($this->agent_id);
            if ($name !== null && $as_html) {
                $name = htmlspecialchars($name);
            }
            if ($name === null) {
                $name = "<error>Unknown #{$this->agent_id}</error>";
            }

            $ret = '<span class="with-agent" data-agent-id="'.$this->agent_id.'">'.$tr->phrase('agent.tickets.assign_to_agent_action', ['agent' => $name]).'</span>';

            return $ret;
        }
    }
}
