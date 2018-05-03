<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class TicketLogsVoter.
 */
class TicketLogsVoter extends AbstractTicketsVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return TicketLog::class;
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
                return $this->getTicketChecker($user)->canModify($ticket, 'fields');
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
            default:
                return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
