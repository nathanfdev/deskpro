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

use Application\AuthBundle\Permissions\PermissionsBag;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\AuthBundle\Voter\Portal\ContentRatingsVoter;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\AuthBundle\Voter\Portal\ProfileVoter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin \Application\AuthBundle\Voter\Portal\ProfileVoter
 */
class ProfileVoterSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        Person $person,
        TokenInterface $token,
        Person $person2
    )
    {
        $this->beConstructedWith($container);
    }

    function it_allows_if_token_represents_this_user(
        TokenInterface $token,
        Person $person
    )
    {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $this->verifyGrantedVote(ProfileVoter::EDIT_PROFILE, $token, $person);
    }

    function it_denies_others(
        TokenInterface $token,
        Person $person,
        Person $person2
    )
    {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $person2->getId()->willReturn(2);

        $this->verifyDeniedVote(ProfileVoter::EDIT_PROFILE, $token, $person2);
    }

    function it_abstains_from_non_profile_votes(
        TokenInterface $token,
        Person $person
    )
    {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);

        $this->verifyAbstainVote(ContentRatingsVoter::RATE_ARTICLES, $token, $person);
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
