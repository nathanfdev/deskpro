<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;

/**
 * Class AbstractTicketsVoter.
 */
abstract class AbstractTicketsVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * @param Person $user
     *
     * @return TicketChecker
     */
    protected function getTicketChecker(Person $user)
    {
        return $user->PermissionsManager->TicketChecker;
    }

    /**
     * @param Person $user
     *
     * @return bool
     */
    protected function canUseTickets(Person $user)
    {
        return $user->hasPerm('agent_tickets.use');
    }

    /**
     * @param Person $user
     * @param Ticket $ticket
     *
     * @return bool
     */
    protected function canModifyTicket(Person $user, Ticket $ticket)
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
