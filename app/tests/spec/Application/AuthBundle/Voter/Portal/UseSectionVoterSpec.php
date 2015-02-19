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

use Application\AuthBundle\Voter\Portal\UseSectionVoter;
use Application\DeskPRO\NewSettings\SettingsBag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Application\AuthBundle\Voter\Portal\ContentAccessVoter;
use Application\AuthBundle\Permissions\PermissionsBag;
use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\AuthBundle\Voter\Portal\ContentCommentVoter;
use Application\AuthBundle\Voter\Portal\ContentRatingsVoter;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin \Application\AuthBundle\Voter\Portal\UseSectionVoter
 */
class UseSectionVoterSpec extends ObjectBehavior
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

    function it_abstains_from_non_section_votes(
        TokenInterface $token
    )
    {
        $this->verifyAbstainVote(ContentAccessVoter::VIEW_FEEDBACK, $token);
    }

    function it_denies_use_if_brand_disabled(
        TokenInterface $token,
        BrandContainer $brand_container
    )
    {
        $brand_container->getSetting('core.apps_kb', Argument::any())->willReturn(false);
        $brand_container->getSetting('core.apps_feedback', Argument::any())->willReturn(false);
        $brand_container->getSetting('core.apps_chat', Argument::any())->willReturn(false);
        $brand_container->getSetting('core.apps_downloads', Argument::any())->willReturn(false);
        $brand_container->getSetting('core.apps_news', Argument::any())->willReturn(false);

        $this->verifyDeniedVote(
            array(
                UseSectionVoter::USE_ARTICLES,
                UseSectionVoter::USE_FEEDBACK,
                UseSectionVoter::USE_CHAT,
                UseSectionVoter::USE_DOWNLOADS,
                UseSectionVoter::USE_NEWS
            ),
            $token
        );
    }

    function it_grants_tickets_if_token_user_has_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $person_permission_bag->get('tickets.use')->willReturn(true);
        $this->verifyGrantedVote(UseSectionVoter::USE_TICKETS, $token);

        $guest_permission_bag->get('tickets.use')->willReturn(true);
        $this->verifyGrantedVote(UseSectionVoter::USE_TICKETS, $guest_token);
    }

    function it_denies_tickets_if_token_user_has_no_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag
    )
    {
        $person_permission_bag->get('tickets.use')->willReturn(false);
        $this->verifyDeniedVote(UseSectionVoter::USE_TICKETS, $token);

        $guest_permission_bag->get('tickets.use')->willReturn(false);
        $this->verifyDeniedVote(UseSectionVoter::USE_TICKETS, $guest_token);
    }

    function it_grants_chat_if_token_user_has_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag,
        BrandContainer $brand_container
    )
    {
        $brand_container->getSetting('core.apps_chat', Argument::any())->willReturn(true);

        $person_permission_bag->get('chat.use')->willReturn(true);
        $this->verifyGrantedVote(UseSectionVoter::USE_CHAT, $token);

        $guest_permission_bag->get('chat.use')->willReturn(true);
        $this->verifyGrantedVote(UseSectionVoter::USE_CHAT, $guest_token);
    }

    function it_denies_chat_if_token_user_has_no_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag,
        BrandContainer $brand_container
    )
    {
        $brand_container->getSetting('core.apps_chat', Argument::any())->willReturn(true);

        $person_permission_bag->get('chat.use')->willReturn(false);
        $this->verifyDeniedVote(UseSectionVoter::USE_CHAT, $token);

        $guest_permission_bag->get('chat.use')->willReturn(false);
        $this->verifyDeniedVote(UseSectionVoter::USE_CHAT, $guest_token);
    }

    function it_denies_content_if_token_user_has_no_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag,
        BrandContainer $brand_container
    )
    {
        $brand_container->getSetting('core.apps_kb', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_feedback', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_downloads', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_news', Argument::any())->willReturn(true);

        $person_permission_bag->get('articles.use')->willReturn(false);
        $person_permission_bag->get('feedback.use')->willReturn(false);
        $person_permission_bag->get('downloads.use')->willReturn(false);
        $person_permission_bag->get('news.use')->willReturn(false);

        $guest_permission_bag->get('articles.use')->willReturn(false);
        $guest_permission_bag->get('feedback.use')->willReturn(false);
        $guest_permission_bag->get('downloads.use')->willReturn(false);
        $guest_permission_bag->get('news.use')->willReturn(false);

        $this->verifyDeniedVote(UseSectionVoter::USE_ARTICLES, $token);
        $this->verifyDeniedVote(UseSectionVoter::USE_FEEDBACK, $token);
        $this->verifyDeniedVote(UseSectionVoter::USE_DOWNLOADS, $token);
        $this->verifyDeniedVote(UseSectionVoter::USE_NEWS, $token);

        $this->verifyDeniedVote(UseSectionVoter::USE_ARTICLES, $guest_token);
        $this->verifyDeniedVote(UseSectionVoter::USE_FEEDBACK, $guest_token);
        $this->verifyDeniedVote(UseSectionVoter::USE_DOWNLOADS, $guest_token);
        $this->verifyDeniedVote(UseSectionVoter::USE_NEWS, $guest_token);
    }

    function it_grants_content_if_token_user_has_permission(
        TokenInterface $token,
        PermissionsBag $person_permission_bag,
        TokenInterface $guest_token,
        PermissionsBag $guest_permission_bag,
        BrandContainer $brand_container
    )
    {
        $brand_container->getSetting('core.apps_kb', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_feedback', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_downloads', Argument::any())->willReturn(true);
        $brand_container->getSetting('core.apps_news', Argument::any())->willReturn(true);

        $person_permission_bag->get('articles.use')->willReturn(true);
        $person_permission_bag->get('feedback.use')->willReturn(true);
        $person_permission_bag->get('downloads.use')->willReturn(true);
        $person_permission_bag->get('news.use')->willReturn(true);

        $guest_permission_bag->get('articles.use')->willReturn(true);
        $guest_permission_bag->get('feedback.use')->willReturn(true);
        $guest_permission_bag->get('downloads.use')->willReturn(true);
        $guest_permission_bag->get('news.use')->willReturn(true);

        $this->verifyGrantedVote(UseSectionVoter::USE_ARTICLES, $token);
        $this->verifyGrantedVote(UseSectionVoter::USE_FEEDBACK, $token);
        $this->verifyGrantedVote(UseSectionVoter::USE_DOWNLOADS, $token);
        $this->verifyGrantedVote(UseSectionVoter::USE_NEWS, $token);

        $this->verifyGrantedVote(UseSectionVoter::USE_ARTICLES, $guest_token);
        $this->verifyGrantedVote(UseSectionVoter::USE_FEEDBACK, $guest_token);
        $this->verifyGrantedVote(UseSectionVoter::USE_DOWNLOADS, $guest_token);
        $this->verifyGrantedVote(UseSectionVoter::USE_NEWS, $guest_token);
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
