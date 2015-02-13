<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\Application\AuthBundle\Voter\Portal;

use Application\AuthBundle\Voter\Portal\ContentRatingsVoter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\AuthBundle\Voter\Portal\TicketsVoter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zend\Session\Config\ConfigInterface;

/**
 * @mixin \Application\AuthBundle\Voter\Portal\TicketsVoter
 */
class TicketsVoterSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        TokenInterface $token,
        Person $person,
        Ticket $ticket
    )
    {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $this->beConstructedWith($container);
    }

    function it_abstains_from_non_ticket_votes(
        TokenInterface $token,
        Ticket $ticket
    )
    {
        $this->verifyAbstainVote(ContentRatingsVoter::RATE_ARTICLES, $token, $ticket);
    }

    function it_denies_list_view_if_not_logged_in(
        TokenInterface $unauthenticated_token,
        Ticket $ticket
    )
    {
        $unauthenticated_token->getUser()->willReturn(null);

        $this->verifyDeniedVote(TicketsVoter::TICKET_LIST, $unauthenticated_token, $ticket);
    }

    function it_grants_list_view_if_logged_in(
        TokenInterface $token,
        Ticket $ticket
    )
    {
        $this->verifyGrantedVote(TicketsVoter::TICKET_LIST, $token, $ticket);
    }

    function it_denies_view_if_not_involved_with_ticket(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_VIEW, $token, $ticket);
    }

    function it_grants_view_if_involved(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(true);

        $this->verifyGrantedVote(TicketsVoter::TICKET_VIEW, $token, $ticket);
    }

    function it_denies_edit_if_not_involved_with_ticket(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    function it_denies_edit_if_only_a_participant(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(true);
        $ticket->isParticipant($person)->willReturn(true);
        $ticket->isOwner($person)->willReturn(false);
        $ticket->isOrganizationManager($person)->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    function it_grants_edit_if_ticket_owner(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(true);
        $ticket->isParticipant($person)->willReturn(false);
        $ticket->isOwner($person)->willReturn(true);
        $ticket->isOrganizationManager($person)->willReturn(false);

        $this->verifyGrantedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    function it_grants_edit_if_organization_manager(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    )
    {
        $ticket->isInvolved($person)->willReturn(true);
        $ticket->isParticipant($person)->willReturn(false);
        $ticket->isOwner($person)->willReturn(false);
        $ticket->isOrganizationManager($person)->willReturn(true);

        $this->verifyGrantedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }





    function verifyGrantedVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function verifyDeniedVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function verifyAbstainVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
