<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class TicketsVoter.
 */
class TicketsVoter extends AbstractTicketsVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Ticket::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$this->canUseTickets($user)) {
            return false;
        }

        /** @var Ticket $ticket */
        $ticket = $context->getParent();

        switch ($attribute) {
            case PermissionGroupVoter::VIEW:
                return $this->getTicketChecker($user)->canView($ticket);
            case PermissionGroupVoter::CREATE:
                return $user->hasPerm('agent_tickets.create');
            case PermissionGroupVoter::MODIFY:
                return $this->canModify($user, $ticket);
            case PermissionGroupVoter::DELETE:
                return $this->getTicketChecker($user)->canDelete($ticket);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }

    /**
     * @param Person $user
     * @param Ticket $ticket
     *
     * @return bool
     */
    private function canModify(Person $user, Ticket $ticket)
    {
        if (!$this->getTicketChecker($user)->canView($ticket)) {
            return false;
        }

        $ticketAgent = $ticket->getAgent();
        $ticketTeam  = $ticket->getAgentTeam();

        if (($ticketAgent && $ticketAgent === $user) || ($ticketTeam && $ticketTeam->hasMember($user))) {
            $type = 'own';
        } elseif (!$ticketAgent && !$ticketTeam) {
            $type = 'unassigned';
        } elseif ($ticket->hasParticipantPerson($user)) {
            $type = 'followed';
        } else {
            $type = 'others';
        }

        return $user->hasPerm('agent_tickets.modify_'.$type);
    }
}
