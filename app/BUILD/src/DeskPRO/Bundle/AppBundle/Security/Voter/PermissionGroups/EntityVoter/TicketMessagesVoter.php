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
    const DELETE_RECORDING = 'delete_recording';

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
        /** @var TicketMessage $message */
        $message = $context->getChild();

        switch ($attribute) {
            case PermissionGroupVoter::VIEW_LIST:
            case PermissionGroupVoter::VIEW:
                return $this->getTicketChecker($user)->canView($ticket);
            case PermissionGroupVoter::CREATE:
                if ($message->isAgentNote()) {
                    return $this->getTicketChecker($user)->canModify($ticket, 'notes');
                } else {
                    return $this->getTicketChecker($user)->canReply($ticket);
                }
            case PermissionGroupVoter::MODIFY:
                if (!$message) {
                    return false;
                }

                return $this->getTicketChecker($user)->canEditMessage($message);
            case PermissionGroupVoter::DELETE:
                if (!$message) {
                    return false;
                }

                return $this->getTicketChecker($user)->canDeleteMessage($message);
            case self::DELETE_RECORDING:
                return $this->getTicketChecker($user)->canModifyMessages($ticket, 'delete_voice_recordings')
                        || $this->getTicketChecker($user)->canModifyMessages($ticket, 'delete_voice_messages');
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
