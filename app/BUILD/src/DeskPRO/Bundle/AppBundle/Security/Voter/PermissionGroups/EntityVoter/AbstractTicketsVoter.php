<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
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
}
