<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentRatingsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ProfileVoter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ProfileVoter
 */
class ProfileVoterSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        Person $person,
        TokenInterface $token,
        Person $person2
    ) {
        $this->beConstructedWith($container);
    }

    public function it_allows_if_token_represents_this_user(
        TokenInterface $token,
        Person $person
    ) {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $this->verifyGrantedVote(ProfileVoter::EDIT_PROFILE, $token, $person);
    }

    public function it_denies_others(
        TokenInterface $token,
        Person $person,
        Person $person2
    ) {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $person2->getId()->willReturn(2);

        $this->verifyDeniedVote(ProfileVoter::EDIT_PROFILE, $token, $person2);
    }

    public function it_abstains_from_non_profile_votes(
        TokenInterface $token,
        Person $person
    ) {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $this->verifyAbstainVote(ContentRatingsVoter::RATE_ARTICLE, $token, $person);
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
