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
use Application\AuthBundle\Voter\Portal\ContentCommentVoter;
use Application\AuthBundle\Voter\Portal\ContentRatingsVoter;
use Application\DeskPRO\Brand\BrandContainer;
use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @mixin ContentSubscriptionsVoter
 */
class ContentSubscriptionsVoterSpec extends ObjectBehavior
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

    public function it_abstains_from_non_subscription_votes(
        TokenInterface $token
    )
    {
        $this->verifyAbstainVote(ContentCommentVoter::COMMENT_ARTICLES, $token);
    }

    public function it_denies_all_guests(
        TokenInterface $guest_token
    )
    {
        $this->verifyDeniedVote(
            array(
                ContentSubscriptionsVoter::SUBSCRIBE_ARTICLES,
                ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES,
                ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS,
                ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES,
                ContentSubscriptionsVoter::SUBSCRIBE_NEWS,
                ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORIES,
                ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS,
                ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES
            ),
            $guest_token
        );
    }

    function it_grants_articles_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.kb_subscriptions', Argument::any())->willReturn(true);

        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLES, $token);
        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES, $token);
    }

    function it_denies_articles_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.kb_subscriptions', Argument::any())->willReturn(false);

        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLES, $token);
        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES, $token);
    }

    function it_grants_downloads_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.downloads_subscriptions', Argument::any())->willReturn(true);

        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS, $token);
        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES, $token);
    }

    function it_denies_downloads_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.downloads_subscriptions', Argument::any())->willReturn(false);

        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS, $token);
        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_DOWNLOADS_CATEGORIES, $token);
    }

    function it_grants_news_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.news_subscriptions', Argument::any())->willReturn(true);

        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_NEWS, $token);
        $this->verifyGrantedVote(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORIES, $token);
    }

    function it_denies_news_and_category_subs_by_brand_setting(
        BrandContainer $brand_container,
        TokenInterface $token
    )
    {
        $brand_container->getSetting('user.news_subscriptions', Argument::any())->willReturn(false);

        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_NEWS, $token);
        $this->verifyDeniedVote(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORIES, $token);
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
