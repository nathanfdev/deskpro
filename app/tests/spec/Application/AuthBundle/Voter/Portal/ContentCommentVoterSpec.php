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
 * @mixin \Application\AuthBundle\Voter\Portal\ContentCommentVoter
 */
class ContentCommentVoterSpec extends ObjectBehavior
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

    function it_abstains_from_non_comment_attributes(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $this->verifyAbstainVote(ContentRatingsVoter::RATE_ARTICLES, $token);
    }

    function it_will_deny_user_if_user_publish_settings_is_false(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(false);

        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_ARTICLES, $token);
    }

    function it_will_deny_guest_if_user_publish_settings_is_false(
        BrandContainer $brand_container,
        TokenInterface $guest_token
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(false);
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(false);

        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_ARTICLES, $guest_token);
    }

    function it_will_deny_if_guest_and_interact_require_login_setting(
        BrandContainer $brand_container,
        TokenInterface $guest_token
    )
    {
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(true);

        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_ARTICLES, $guest_token);
    }

    function it_will_deny_guest_comments_if_permissions_fail(
        BrandContainer $brand_container,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(true);
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(false);

        $guest_permission_bag->get('articles.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_ARTICLES, $guest_token);

        $guest_permission_bag->get('feedback.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_FEEDBACK, $guest_token);

        $guest_permission_bag->get('downloads.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_DOWNLOADS, $guest_token);

        $guest_permission_bag->get('news.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_NEWS, $guest_token);
    }

    function it_will_grant_guest_comments_if_permissions_pass(
        BrandContainer $brand_container,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(true);
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(false);

        $guest_permission_bag->get('articles.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_ARTICLES, $guest_token);

        $guest_permission_bag->get('feedback.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_FEEDBACK, $guest_token);

        $guest_permission_bag->get('downloads.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_DOWNLOADS, $guest_token);

        $guest_permission_bag->get('news.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_NEWS, $guest_token);
    }

    function it_will_deny_user_comments_if_permissions_fail(
        BrandContainer $brand_container,
        TokenInterface $token,
        PermissionsBag $person_permission_bag
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(true);

        $person_permission_bag->get('articles.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_ARTICLES, $token);

        $person_permission_bag->get('feedback.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_FEEDBACK, $token);

        $person_permission_bag->get('downloads.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_DOWNLOADS, $token);

        $person_permission_bag->get('news.comment')->willReturn(false);
        $this->verifyDeniedVote(ContentCommentVoter::COMMENT_NEWS, $token);
    }

    function it_will_grant_user_comments_if_permissions_pass(
        BrandContainer $brand_container,
        TokenInterface $token,
        PermissionsBag $person_permission_bag
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(true);

        $person_permission_bag->get('articles.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_ARTICLES, $token);

        $person_permission_bag->get('feedback.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_FEEDBACK, $token);

        $person_permission_bag->get('downloads.comment')->willReturn(true);
        $this->verifyGrantedVote(ContentCommentVoter::COMMENT_DOWNLOADS, $token);

        $person_permission_bag->get('news.comment')->willReturn(true);
        $attribute = ContentCommentVoter::COMMENT_NEWS;
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, null, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }




    function verifyGrantedVote($attribute, $token)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, null, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function verifyDeniedVote($attribute, $token)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, null, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function verifyAbstainVote($attribute, $token)
    {
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $this->vote($token, null, $attribute)
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
