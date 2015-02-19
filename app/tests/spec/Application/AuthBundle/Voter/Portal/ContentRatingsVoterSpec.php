<?php

namespace spec\Application\AuthBundle\Voter\Portal;

use Application\AuthBundle\Permissions\PermissionsBag;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\AuthBundle\Voter\Portal\ContentCommentVoter;
use Application\AuthBundle\Voter\Portal\ContentRatingsVoter;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin \Application\AuthBundle\Voter\Portal\ContentRatingsVoter
 */
class ContentRatingsVoterSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        PortalPermissionsManager $permissions_manager,
        BrandStack $brand_stack,
        BrandContainer $brand_container,
        Person $person,
        TokenInterface $token,
        TokenInterface $guest_token,
        PermissionsBag $person_permission_bag,
        PermissionsBag $guest_permission_bag
    )
    {
        $person->getId()->willReturn(1);
        $token->getUser()->willReturn($person);
        $guest_token->getUser()->willReturn(null);
        $brand_stack->getActive()->willReturn($brand_container);
        $container->get('brand_stack')->willReturn($brand_stack);
        $container->get('portal_permissions_manager')->willReturn($permissions_manager);
        $permissions_manager->getPermissionsBagForGuest()->willReturn($guest_permission_bag);
        $permissions_manager->getPermissionsBagForPerson($person)->willReturn($person_permission_bag);

        $this->beConstructedWith($container);
    }

    function it_abstains_from_non_rating_attributes(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $this->vote($token, null, array(ContentCommentVoter::COMMENT_ARTICLES))->shouldBe(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_will_deny_if_guest_and_interact_require_login_setting(
        BrandContainer $brand_container,
        TokenInterface $guest_token
    )
    {
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(true);

        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_ARTICLES))->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_will_deny_guest_comments_if_permissions_fail(
        BrandContainer $brand_container,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(false);

        $guest_permission_bag->get('articles.rate')->willReturn(false);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_ARTICLES))->shouldBe(VoterInterface::ACCESS_DENIED);

        $guest_permission_bag->get('feedback.rate')->willReturn(false);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_FEEDBACK))->shouldBe(VoterInterface::ACCESS_DENIED);

        $guest_permission_bag->get('downloads.rate')->willReturn(false);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_DOWNLOADS))->shouldBe(VoterInterface::ACCESS_DENIED);

        $guest_permission_bag->get('news.rate')->willReturn(false);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_NEWS))->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_will_grant_guest_comments_if_permissions_pass(
        BrandContainer $brand_container,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(false);

        $guest_permission_bag->get('articles.rate')->willReturn(true);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_ARTICLES))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $guest_permission_bag->get('feedback.rate')->willReturn(true);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_FEEDBACK))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $guest_permission_bag->get('downloads.rate')->willReturn(true);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_DOWNLOADS))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $guest_permission_bag->get('news.rate')->willReturn(true);
        $this->vote($guest_token, null, array(ContentRatingsVoter::RATE_NEWS))->shouldBe(VoterInterface::ACCESS_GRANTED);
    }

    function it_will_deny_user_comments_if_permissions_fail(
        BrandContainer $brand_container,
        TokenInterface $token,
        PermissionsBag $person_permission_bag
    )
    {
        $person_permission_bag->get('articles.rate')->willReturn(false);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_ARTICLES))->shouldBe(VoterInterface::ACCESS_DENIED);

        $person_permission_bag->get('feedback.rate')->willReturn(false);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_FEEDBACK))->shouldBe(VoterInterface::ACCESS_DENIED);

        $person_permission_bag->get('downloads.rate')->willReturn(false);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_DOWNLOADS))->shouldBe(VoterInterface::ACCESS_DENIED);

        $person_permission_bag->get('news.rate')->willReturn(false);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_NEWS))->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_will_grant_user_comments_if_permissions_pass(
        BrandContainer $brand_container,
        TokenInterface $token,
        PermissionsBag $person_permission_bag
    )
    {
        $person_permission_bag->get('articles.rate')->willReturn(true);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_ARTICLES))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $person_permission_bag->get('feedback.rate')->willReturn(true);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_FEEDBACK))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $person_permission_bag->get('downloads.rate')->willReturn(true);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_DOWNLOADS))->shouldBe(VoterInterface::ACCESS_GRANTED);

        $person_permission_bag->get('news.rate')->willReturn(true);
        $this->vote($token, null, array(ContentRatingsVoter::RATE_NEWS))->shouldBe(VoterInterface::ACCESS_GRANTED);
    }
}
