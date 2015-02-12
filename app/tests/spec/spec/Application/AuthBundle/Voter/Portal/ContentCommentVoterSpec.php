<?php

namespace spec\Application\AuthBundle\Voter\Portal;

use Application\AuthBundle\Permissions\PermissionsBag;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

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

    function it_will_deny_if_user_publish_settings_is_false(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.publish_comments', false)->willReturn(false);

        $this->vote($token, null, array('COMMENT_ARTICLES'))->shouldBe(VoterInterface::ACCESS_DENIED);
    }

    function it_will_deny_if_guest_and_interact_require_login_setting(
        BrandContainer $brand_container,
        TokenInterface $guest_token,
        PermissionsBag $person_permission_bag
    )
    {
        $brand_container->getSetting('core.interact_require_login', false)->willReturn(true);

        $this->vote($guest_token, null, array('COMMENT_ARTICLES'))->shouldBe(VoterInterface::ACCESS_DENIED);
    }
}
