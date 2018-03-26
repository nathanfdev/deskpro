<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class TicketMessagesVoter.
 */
class TicketMessagesVoter extends AbstractTicketsVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return TicketMessage::class;
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
            case PermissionGroupVoter::VIEW_LIST:
            case PermissionGroupVoter::VIEW:
                return $this->getTicketChecker($user)->canView($ticket);
            case PermissionGroupVoter::CREATE:
                return $this->getTicketChecker($user)->canReply($ticket);
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $this->getTicketChecker($user)->canEditMessages($ticket);
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
