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
     * Approval attributes
     */
    const ADD_APPROVAL = 'add_approval';
    const CANCEL_APPROVAL = 'cancel_approval';

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
                return $this->canModifyTicket($user, $ticket);
            case PermissionGroupVoter::DELETE:
                return $this->getTicketChecker($user)->canDelete($ticket);
            case self::ADD_APPROVAL:
                return $this->getTicketChecker($user)->canAddApproval($ticket);
            case self::CANCEL_APPROVAL:
                return $this->getTicketChecker($user)->canCancelApproval($ticket);
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
}
