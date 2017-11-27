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

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;

/**
 * Class TicketsVoter.
 */
class TicketFollowUpVoter extends AbstractTicketsVoter
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return TicketFollowUp::class;
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
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                return $this->canModify($user, $ticket);
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
