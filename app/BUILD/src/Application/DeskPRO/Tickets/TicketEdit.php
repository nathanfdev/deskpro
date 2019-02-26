<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

class TicketEdit implements PersonContextInterface
{
    /**
     * Application\DeskPRO\Entity\Ticket.
     */
    protected $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person_context;

    /**
     * @var array
     */
    protected $perm_errors = [];

    public function __construct(Entity\Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person_context = $person;
    }

    /**
     * Apply a standard actions array to this ticket.
     *
     * @param array $actions
     */
    public function applyActions(array $actions)
    {
        $this->perm_errors = [];
        $return            = [];

        if ($this->person_context) {
            $tcheck = $this->person_context->PermissionsManager->TicketChecker;
        } else {
            $tcheck = null;
        }
        /* @var $tcheck \Application\DeskPRO\People\PermissionChecker\TicketChecker */

        foreach ($actions as $term => $action) {
            $term_id = null;

            // $term of ticket_field[12] becomes $term=ticket_field, $term_id=12
            $m = null;
            if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
                $term    = $m[1];
                $term_id = $m[2];
            }

            switch ($term) {
                case 'department_id':
                    if ($this->ticket->getDepartmentId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'department')) {
                            $this->perm_errors[] = 'department';
                            break;
                        }
                    }
                    $this->ticket['department_id'] = $action;
                    break;

                case 'language_id':
                    if ($this->ticket->getLanguageId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'language';
                            break;
                        }
                    }
                    $this->ticket['language_id'] = $action;
                    break;

                case 'category_id':
                    if ($this->ticket->getCategoryId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'category';
                            break;
                        }
                    }
                    $this->ticket['category_id'] = $action;
                    break;

                case 'agent':
                case 'agent_id':

                    if ($this->ticket->getAgentId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        $can = true;
                        if ($action == $this->person_context->getId()) {
                            if (!$tcheck->canModify($this->ticket, 'assign_self')) {
                                $can = null;
                            }
                        } elseif (!$tcheck->canModify($this->ticket, 'assign_agent')) {
                            $can = null;
                        }

                        if (!$can) {
                            $this->perm_errors[] = 'agent';
                            break;
                        }
                    }

                    $this->ticket['agent_id'] = $action;
                    break;

                case 'agent_team':
                case 'agent_team_id':

                    if ($this->ticket->getAgentTeamId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        $team = true;
                        // it seems that it should check assign_team only, otherwise you never can change team
                        // cause in UI dropdown shows only your teams
                        if (!$tcheck->canModify($this->ticket, 'assign_team')) {
                            $team = null;
                        }

                        if (!$team) {
                            $this->perm_errors[] = 'team';
                            break;
                        }
                    }

                    $this->ticket['agent_team_id'] = $action;
                    break;

                case 'product_id':

                    if ($this->ticket->getProductId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'product';
                            break;
                        }
                    }
                    $this->ticket['product_id'] = $action;
                    break;

                case 'priority_id':

                    if ($this->ticket->getPriorityId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'priority';
                            break;
                        }
                    }
                    $this->ticket['priority_id'] = $action;
                    break;

                case 'workflow_id':

                    if ($this->ticket->getWorkflowId() == $action) {
                        break;
                    }

                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'workflow';
                            break;
                        }
                    }
                    $this->ticket['workflow_id'] = $action;
                    break;

                case 'status':
                    if ($this->person_context) {
                        $status       = true;
                        $ticketStatus = App::getcontainer()->getTicketStatuses()->findStatusOrException($action);

                        // Switching to or from archived
                        if (($ticketStatus->getStatusType() == 'archived' || $this->ticket->status == 'archived') && !$tcheck->canSetArchived($this->ticket)) {
                            $status = null;
                        }
                        if ($ticketStatus->getStatusType() == 'resolved' && !$tcheck->canModify($this->ticket, 'set_resolved')) {
                            $status = null;
                        }
                        if ($ticketStatus->getStatusType() == 'awaiting_agent' && !$tcheck->canModify($this->ticket, 'set_awaiting_agent')) {
                            $status = null;
                        }
                        if ($ticketStatus->getStatusType() == 'awaiting_user' && !$tcheck->canModify($this->ticket, 'set_awaiting_user')) {
                            $status = null;
                        }
                        if ($ticketStatus->getStatusType() == 'pending' && !$tcheck->canModify($this->ticket, 'set_hold')) {
                            $status = null;
                        }
                        if (!$status) {
                            $this->perm_errors[] = 'status';
                            break;
                        }
                        if (isset($actions['hidden_status'])) {
                            $action .= '.'.$actions['hidden_status'];
                        }

                        $ticketStatus = App::getcontainer()->getTicketStatuses()->findStatusOrException($action);
                    }

                    $this->ticket->setTicketStatus($ticketStatus);
                    break;

                case 'is_hold':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'set_hold')) {
                            $this->perm_errors[] = 'hold';
                            break;
                        }
                    }
                    $this->ticket['is_hold'] = $action;
                    break;

                case 'flag':
                    $agent = App::getCurrentPerson();

                    if (!$agent) {
                        continue;
                    }

                    $this->ticket->setFlagForPerson($agent, $action);
                    break;

                case 'add_labels':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'labels')) {
                            $this->perm_errors[] = 'label';
                            break;
                        }
                    }
                    foreach ((array) $action as $label) {
                        $this->ticket->getLabelManager()->addLabel($label);
                    }
                    break;

                case 'remove_labels':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'labels')) {
                            $this->perm_errors[] = 'label';
                            break;
                        }
                    }
                    foreach ((array) $action as $label) {
                        $this->ticket->getLabelManager()->removeLabel($label);
                    }
                    break;

                case 'add_participant':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'cc')) {
                            $this->perm_errors[] = 'cc';
                            break;
                        }
                    }

                    $person = App::getEntityRepository('DeskPRO:Perosn')->find($action['add_participant']);

                    if (!$person) {
                        continue;
                    }

                    $this->ticket->addParticipant($person);

                    break;

                case 'new_reply':
                    if ($this->person_context) {
                        if (!$tcheck->canReply($this->ticket)) {
                            break;
                        }
                    }
                    $agent = App::getCurrentPerson();

                    if (!$agent) {
                        continue;
                    }

                    $message            = new Entity\TicketMessage();
                    $message['person']  = $agent;
                    $message['ticket']  = $this->ticket;
                    $message['message'] = $action['new_reply'];

                    $this->ticket->addMessage($message);

                    $return['new_reply'] = $message;

                    break;

                case 'ticket_field':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            $this->perm_errors[] = 'ticket field';
                            break;
                        }
                    }
                    $field = App::getEntityRepository('DeskPRO:CustomDefTicket')->find($term_id);
                    foreach ($field->getHandler()->getDataFromForm($action['value']) as $info) {
                        $this->ticket->setCustomDataField($info[0], $info[1], $info[2]);
                    }

                    break;

                case 'urgency':
                    if ($this->person_context) {
                        if (!$tcheck->canModify($this->ticket, 'fields')) {
                            break;
                        }
                    }
                    $this->ticket->urgency = $action;
                    break;
            }
        }

        return $return;
    }

    public function getPermErrors()
    {
        return $this->perm_errors;
    }

    public function addMessage(Entity\TicketMessage $message)
    {
        $this->ticket->addMessage($message);
    }

    public function setCustomDataAll(array $ticket_field_datas)
    {
        foreach ($ticket_field_datas as $info) {
            $this->ticket->setCustomDataField($info[0], $info[1], $info[2]);
        }
    }

    public function save()
    {
        App::getOrm()->persist($this->ticket);
        App::getOrm()->flush();
    }
}
