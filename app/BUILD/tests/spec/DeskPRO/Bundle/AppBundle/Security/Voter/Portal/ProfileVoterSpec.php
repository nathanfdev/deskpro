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
