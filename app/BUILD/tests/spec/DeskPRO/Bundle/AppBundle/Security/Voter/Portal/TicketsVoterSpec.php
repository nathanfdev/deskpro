<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Security\Voter\Portal\TicketsVoter
 */
class TicketsVoterSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        TokenInterface $token,
        Person $person,
        Ticket $ticket
    ) {
        $person->getId()->willReturn(1);
        $person->isAgent()->willReturn(false);
        $token->getUser()->willReturn($person);

        $this->beConstructedWith($container);
    }

    public function it_abstains_from_non_ticket_votes(
        TokenInterface $token,
        Ticket $ticket
    ) {
        $this->verifyAbstainVote(ContentRatingsVoter::RATE_ARTICLE, $token, $ticket);
    }

    public function it_denies_list_view_if_not_logged_in(
        TokenInterface $unauthenticated_token,
        Ticket $ticket
    ) {
        $unauthenticated_token->getUser()->willReturn(null);

        $this->verifyDeniedVote(TicketsVoter::TICKET_LIST, $unauthenticated_token, $ticket);
    }

    public function it_grants_list_view_if_logged_in(
        TokenInterface $token,
        Ticket $ticket
    ) {
        $this->verifyGrantedVote(TicketsVoter::TICKET_LIST, $token, $ticket);
    }

    public function it_denies_view_if_not_involved_with_ticket(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_VIEW, $token, $ticket);
    }

    public function it_grants_view_if_involved(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(true);

        $this->verifyGrantedVote(TicketsVoter::TICKET_VIEW, $token, $ticket);
    }

    public function it_denies_edit_if_not_involved_with_ticket(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    public function it_denies_edit_if_only_a_participant(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(true);
        $ticket->isParticipant($person)->willReturn(true);
        $ticket->isOwner($person)->willReturn(false);
        $ticket->isOrganizationManager($person)->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    public function it_grants_edit_if_ticket_owner_and_ticket_is_visible(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(true);
        $ticket->isParticipant($person)->willReturn(false);
        $ticket->isOwner($person)->willReturn(true);
        $ticket->isOrganizationManager($person)->willReturn(false);
        $ticket->hasVisibleStatus()->willReturn(true);

        $this->verifyGrantedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    public function it_denies_edit_if_ticket_owner_and_ticket_is_not_visible(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(true);
        $ticket->isParticipant($person)->willReturn(false);
        $ticket->isOwner($person)->willReturn(true);
        $ticket->isOrganizationManager($person)->willReturn(false);
        $ticket->hasVisibleStatus()->willReturn(false);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    public function it_denies_edit_if_organization_manager_and_not_owner(
        TokenInterface $token,
        Ticket $ticket,
        Person $person
    ) {
        $person->isAgent()->willReturn(false);
        $ticket->isInvolved($person, 'user')->willReturn(true);
        $ticket->isParticipant($person)->willReturn(false);
        $ticket->isOwner($person)->willReturn(false);
        $ticket->isOrganizationManager($person)->willReturn(true);
        $ticket->hasVisibleStatus()->willReturn(true);

        $this->verifyDeniedVote(TicketsVoter::TICKET_EDIT, $token, $ticket);
    }

    public function verifyGrantedVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    public function verifyDeniedVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    public function verifyAbstainVote($attribute, $token, $object)
    {
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $this->vote($token, $object, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
