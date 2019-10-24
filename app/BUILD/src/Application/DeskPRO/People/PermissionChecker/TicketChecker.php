<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\Helpers\AgentPermissions;

/**
 * Class TicketChecker.
 */
class TicketChecker extends AbstractChecker
{
    /**
     * @var array
     */
    public static $modify_ops = [
        'set_archived',
        'department',
        'fields',
        'assign_agent',
        'assign_team',
        'assign_self',
        'cc',
        'slas',
        'merge',
        'labels',
        'notes',
        'set_hold',
        'set_awaiting_user',
        'set_awaiting_agent',
        'set_resolved',
        'set_unresolved',
        'followed',
        'billing',
        'associate_problem',
        'disassociate_problem',
        'add_approval',
        'cancel_approval',
    ];

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     */
    private $agents;

    protected function init()
    {
        $this->person->loadHelper('Agent');
        $this->agents = App::$container->getAgentData();
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function canView(Ticket $ticket)
    {
        //------------------------------
        // If the user is part of the ticket
        // then we know right away they can view
        //------------------------------

        $agent = $ticket->getAgent();
        if ($agent && $agent === $this->person) {
            return true;
        }

        $agentTeam = $ticket->getAgentTeam();
        if ($agentTeam && $this->agents->isAgentMemberOfTeam($this->person, $agentTeam)) {
            return true;
        }

        if ($ticket->hasParticipantPerson($this->person)) {
            return true;
        }

        foreach ($ticket->getApprovals() as $approval) {
            if ($approval->getTemplate()->canApproversViewSubject()) {
                if ($approval->hasApprover($this->person)) {
                    return true;
                }
            }
        }

        if (!$this->person->hasPerm('agent_tickets.use')) {
            return false;
        }

        //------------------------------
        // Can't view certain deps
        //------------------------------

        $this->person->loadHelper('AgentPermissions');

        /** @var AgentPermissions $helper */
        $helper     = $this->person->getHelper('AgentPermissions');
        $department = $ticket->getDepartment();

        if ($department && !$helper->isDepartmentAllowed($department)) {
            return false;
        }

        //------------------------------
        // Cant view unassigned
        //------------------------------

        if (!$agent && !$this->person->hasPerm('agent_tickets.view_unassigned')) {
            return false;
        }

        //------------------------------
        // Cant view others
        //------------------------------

        if ($agent && !$this->person->hasPerm('agent_tickets.view_others')) {
            return false;
        }

        // If we got here, then we're allowed
        return true;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function canDelete(Ticket $ticket)
    {
        if (!$this->canView($ticket)) {
            return false;
        }

        //------------------------------
        // Can delete own
        //------------------------------

        if ($this->person->hasPerm('agent_tickets.delete_own')) {
            if ($ticket->agent && $ticket->agent->id == $this->person->id) {
                return true;
            }

            if ($ticket->agent_team && $this->agents->isAgentMemberOfTeam($this->person, $ticket->agent_team)) {
                return true;
            }
        }

        //------------------------------
        // Can delete unassigned
        //------------------------------

        if (!$ticket->agent && $this->person->hasPerm('agent_tickets.delete_unassigned')) {
            return true;
        }

        //------------------------------
        // Can delete others
        //------------------------------

        if ($ticket->agent && $this->person->hasPerm('agent_tickets.delete_assigned')) {
            return true;
        }

        //------------------------------
        // Can delete others
        //------------------------------

        if ($ticket->agent && $this->person->hasPerm('agent_tickets.delete_others')) {
            return true;
        }

        //------------------------------
        // Can delete followed
        //------------------------------

        if ($ticket->hasParticipantPerson($this->person) && $this->person->hasPerm('agent_tickets.delete_followed')) {
            return true;
        }

        //------------------------------
        // Cant delete
        //------------------------------

        return false;
    }

    /**
     * @return bool
     */
    public function canDeleteAny()
    {
        return $this->person->hasPerm('agent_tickets.delete_own') || $this->person->hasPerm('agent_tickets.delete_unassigned') || $this->person->hasPerm('agent_tickets.delete_assigned') || $this->person->hasPerm('agent_tickets.delete_followed');
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function canReply(Ticket $ticket)
    {
        if (!$this->canView($ticket)) {
            return false;
        }

        $agent = $ticket->getAgent();

        //------------------------------
        // Can delete own
        //------------------------------

        if ($this->person->hasPerm('agent_tickets.reply_own')) {
            if ($agent && $agent === $this->person) {
                return true;
            }

            if ($ticket->getPerson() && $ticket->getPerson() === $this->person) {
                return true;
            }

            if ($ticket->getAgentTeam() && $this->agents->isAgentMemberOfTeam($this->person, $ticket->getAgentTeam())) {
                return true;
            }
        }

        //------------------------------
        // Can delete unassigned
        //------------------------------

        if (!$agent && $this->person->hasPerm('agent_tickets.reply_unassigned')) {
            return true;
        }

        //------------------------------
        // Can delete others
        //------------------------------

        if ($agent && $this->person->hasPerm('agent_tickets.reply_others')) {
            return true;
        }

        //------------------------------
        // Can reply to followed
        //------------------------------

        if ($ticket->hasParticipantPerson($this->person) && $this->person->hasPerm('agent_tickets.reply_to_followed')) {
            return true;
        }

        //------------------------------
        // Cant delete
        //------------------------------

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     */
    public function canSetArchived(Ticket $ticket)
    {
        if (!$this->person->hasPerm('agent_tickets.modify_set_archived')) {
            return false;
        }
        if ($ticket->status == 'resolved' and ($this->canModify($ticket, 'set_awaiting_user') || $this->canModify($ticket, 'set_awaiting_agent'))) {
            return true;
        } elseif ($this->canModify($ticket, 'set_resolved')) {
            return true;
        }

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return bool
     */
    public function canModify(Ticket $ticket, $op)
    {
        if (!$this->canView($ticket)) {
            return false;
        }

        $isSetUnresolved = 'set_awaiting_user' === $op || 'set_awaiting_agent' === $op;
        if ($isSetUnresolved && 'resolved' === $ticket['status'] && !$this->canModify($ticket, 'set_unresolved')) {
            return false;
        }

        if (!in_array($op, self::$modify_ops)) {
            throw new \InvalidArgumentException("Invalid modify permission op: $op");
        }

        //------------------------------
        // Figure out which set of permissions
        // the current ticket falls into
        //------------------------------

        $agent     = $ticket->getAgent();
        $agentTeam = $ticket->getAgentTeam();

        // Own tickets
        if (
            ($agent && $agent === $this->person)
            || ($agentTeam && $this->agents->isAgentMemberOfTeam($this->person, $agentTeam))
        ) {
            $setSuffix = 'own';
        // Unassigned tickets
        } elseif (!$agent && !$agentTeam) {
            $setSuffix = 'unassigned';
        // Other
        } else {
            $setSuffix = 'others';
        }

        $permissionsToCheck = [
            'agent_tickets.modify_'.$setSuffix,
            'agent_tickets.modify_'.$op.'_'.$setSuffix,
        ];

        if ($ticket->hasParticipantPerson($this->person)) {
            $permissionsToCheck[] = 'agent_tickets.modify_followed';
            $permissionsToCheck[] = 'agent_tickets.modify_'.$op.'_followed';
        }

        return array_reduce($permissionsToCheck, [$this, 'permissionsReducer'], false);
    }

    /**
     * Check if two tickets can be merged. To be able to merge, both tickets must give try for the 'merge' permission.
     *
     * @param Ticket $ticket1
     * @param Ticket $ticket2
     *
     * @return bool
     */
    public function canMerge(Ticket $ticket1, Ticket $ticket2)
    {
        /** @var Ticket $ticket */
        foreach ([$ticket1, $ticket2] as $ticket) {
            if (($ticket->agent && $ticket->agent->id == $this->person->id) || ($ticket->agent_team && $this->agents->isAgentMemberOfTeam($this->person, $ticket->agent_team))) {
                $setSuffix = 'own';
            } elseif (!$ticket->agent && !$ticket->agent_team) {
                $setSuffix = 'unassigned';
            } else {
                $setSuffix = 'others';
            }

            $permissionsToCheck = [
                "agent_tickets.modify_merge_{$setSuffix}",
                "agent_tickets.modify_{$setSuffix}",
            ];

            if ($ticket->hasParticipantPerson($this->person)) {
                $permissionsToCheck[] = 'agent_tickets.modify_followed';
                $permissionsToCheck[] = 'agent_tickets.modify_merge_followed';
            }

            if (false === array_reduce($permissionsToCheck, [$this, 'permissionsReducer'], false)) {
                return false;
            }
        }

        return true;
    }

    public function canEditMessage(TicketMessage $message)
    {
        if (!$this->canView($message->getTicket())) {
            return false;
        }

        $permissionsToCheck = [];

        if ($message->isAgentNote()) {
            $permissionsToCheck = ['edit_notes'];
            // Can Edit Notes (Time Limit, 1 hour)
            if (time() - $message->getDateCreated()->getTimestamp() <= 3600) {
                $permissionsToCheck[] = 'edit_timelimited_notes';
            }
        } else {
            $permissionsToCheck = ['edit'];
        }

        return array_reduce($permissionsToCheck, function ($carry, $item) use ($message) {
            return $carry || $this->canModifyMessages($message->getTicket(), $item);
        });
    }

    public function canDeleteMessage(TicketMessage $message)
    {
        if (!$this->canView($message->getTicket())) {
            return false;
        }

        $permissionsToCheck = [];

        if ($message->isVoiceMessage()) {
            $permissionsToCheck[] = 'delete_voice_messages';
        } elseif ($message->isAgentNote()) {
            $permissionsToCheck = ['delete_notes'];
            // Can Edit Notes (Time Limit, 1 hour)
            if (time() - $message->getDateCreated()->getTimestamp() <= 3600) {
                $permissionsToCheck[] = 'delete_timelimited_notes';
            }
        } else {
            $permissionsToCheck[] = 'delete';
        }

        return array_reduce($permissionsToCheck, function ($carry, $item) use ($message) {
            return $carry || $this->canModifyMessages($message->getTicket(), $item);
        });
    }

    /**
     * Check if modify message permission is enabled
     * There is a top level permission agent_tickets.modify_messages_{suffix}
     * if this permission is true - all sub-permissions assumed as true.
     *
     * Check TicketPermissions properties with modify_messages_ prefix to get a list of possible $op
     * $op examples: 'edit', 'edit_notes', 'delete_voice_messages' etc ...
     *
     * @param Ticket $ticket
     * @param string $op
     *
     * @return bool
     */
    public function canModifyMessages(Ticket $ticket, $op)
    {
        if (!$this->canView($ticket)) {
            return false;
        }

        // own/unassigned/other
        $setSuffix = $this->getPermissionsSetSuffix($ticket);

        $permissionsToCheck = [
            'agent_tickets.modify_messages_'.$setSuffix,
            'agent_tickets.modify_messages_'.$op.'_'.$setSuffix,
        ];

        if ($ticket->hasParticipantPerson($this->person)) {
            $permissionsToCheck[] = 'agent_tickets.modify_messages_followed';
            $permissionsToCheck[] = 'agent_tickets.modify_messages_'.$op.'_followed';
        }

        return array_reduce($permissionsToCheck, [$this, 'permissionsReducer'], false);
    }

    /**
     * Figure out which set of permissions
     * the current ticket falls into.
     *
     * @param Ticket $ticket
     */
    protected function getPermissionsSetSuffix(Ticket $ticket)
    {
        $agent     = $ticket->getAgent();
        $agentTeam = $ticket->getAgentTeam();

        // Other
        $suffix = 'others';
        // Own tickets
        if (
            ($agent && $agent === $this->person)
            || ($agentTeam && $this->agents->isAgentMemberOfTeam($this->person, $agentTeam))
        ) {
            $suffix = 'own';
        // Unassigned tickets
        } elseif (!$agent && !$agentTeam) {
            $suffix = 'unassigned';
        }

        return $suffix;
    }

    /**
     * @param $carry
     * @param $item
     *
     * @internal
     *
     * @return bool
     */
    public function permissionsReducer($carry, $item)
    {
        return $carry || $this->person->hasPerm($item);
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function canAssociateProblem(Ticket $ticket)
    {
        return $this->canModify($ticket, 'associate_problem') ?: $this->doCheck($ticket, 'associate_problem');
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function canDisassociateProblem(Ticket $ticket)
    {
        return $this->canModify($ticket, 'disassociate_problem') ?: $this->doCheck($ticket, 'disassociate_problem');
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function canAddApproval(Ticket $ticket)
    {
        return $this->canModify($ticket, 'add_approval') ?: $this->doCheck($ticket, 'add_approval');
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function canCancelApproval(Ticket $ticket)
    {
        return $this->canModify($ticket, 'cancel_approval') ?: $this->doCheck($ticket, 'cancel_approval');
    }

    /**
     * @param Ticket $ticket
     * @param $perm
     *
     * @return bool
     */
    protected function doCheck(Ticket $ticket, $perm)
    {
        if (!$this->canView($ticket)) {
            return false;
        }

        // own
        if ($this->person->hasPerm('agent_tickets.'.$perm.'_own')) {
            if ($ticket->agent && $ticket->agent->id == $this->person->id) {
                return true;
            }

            if ($ticket->agent_team && $this->agents->isAgentMemberOfTeam($this->person, $ticket->agent_team)) {
                return true;
            }
        }

        // unassigned
        if (!$ticket->agent && $this->person->hasPerm('agent_tickets.'.$perm.'_unassigned')) {
            return true;
        }

        // assigned
        if ($ticket->agent && $this->person->hasPerm('agent_tickets.'.$perm.'_assigned')) {
            return true;
        }

        // others
        if ($ticket->agent && $this->person->hasPerm('agent_tickets.'.$perm.'_others')) {
            return true;
        }

        // followed
        if ($ticket->hasParticipantPerson($this->person) && $this->person->hasPerm(
                'agent_tickets.'.$perm.'_followed'
            )
        ) {
            return true;
        }

        return false;
    }
}
