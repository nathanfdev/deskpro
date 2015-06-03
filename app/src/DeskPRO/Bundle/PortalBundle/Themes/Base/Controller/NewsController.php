<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    /**
     * @Tag(name="news_cats_tabs", default_options={"style":"tabs", "from_root": true}, esi=true)
     * @TagHttpCache()
     *
     * @TagOptions(
     *      defaults={
     *          "style": "tabs",
     *          "category": null,
     *          "from_root": false
     *      },
     *      allowed_values={
     *          "style": {"tabs"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options, NewsCategory $category = null)
    {
        if ($options['from_root']) {
            $category_children = $this->getNewsDataService()->getCategoryChildren(null);
        } else {
            $category_children = $this->getNewsDataService()->getCategoryChildren($category);
        }

        return $this->renderThemeView(
            sprintf('Theme:News:CategoryList/%s.html.twig', $options['style']),
            array(
                'category'          => $category,
                'category_children' => $category_children,
            )
        );
    }

    /**
     * @Tag(name="news_list_excerpts", default_options={"style":"excerpts"})
     * @Tag(name="news_list_full", default_options={"style":"full"})
     * @Tag(name="news_list_simple", default_options={"style":"simple"})
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "excerpts",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": false
     *      },
     *      allowed_values={
     *          "style": {"excerpts", "full", "simple"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function listAction(TagRequest $tag_request, array $options, NewsCategory $category = null)
    {
        $pager = $this->getNewsDataService()->getNewsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            sprintf('Theme:News:PostList/%s.html.twig', $options['style']),
            array(
                'pager'              => $pager,
                'category'           => $category,
            )
        );
    }

    /**
     * @Tag(name="news_post", esi=true)
     * @TagHttpCache(content="post")
     *
     * @TagOptions(
     *      defaults={"is_subscribed":false},
     *      required={"post"},
     *      allowed_types={
     *          "post": {"Application\DeskPRO\Entity\News", "int", "string"}
     *      },
     *      attribute_expressions={
     *          "post": "service('data.news').getPost(options['post'])"
     *      }
     * )
     * @Security("is_granted('USE_NEWS')")
     */
    public function postAction(TagRequest $tag_request, array $options, News $post)
    {
        return $this->renderThemeView(
            'Theme:News:PostView/post.html.twig',
            array(
                'post' => $post,
            )
        );
    }

    /**
     * @Tag(name="news_post_subscription", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      required={"post"},
     *      allowed_types={
     *          "post": {"Application\DeskPRO\Entity\News", "int", "string"}
     *      },
     *      attribute_expressions={
     *          "post": "service('data.news').getPost(options['post'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function postSubscriptionAction(TagRequest $tag_request, array $options, News $post)
    {
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($post, $this->getUser());
        }

        return $this->renderThemeView(
            sprintf('Theme:News:PostView/subscription_info.html.twig', $options['style']),
            array(
                'post'          => $post,
                'is_subscribed' => $is_subscribed,
            )
        );
    }

    /**
     * @Tag(name="news_category_subscription", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"category": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\NewsCategory", "int", "string"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function categorySubscriptionAction(TagRequest $tag_request, array $options, NewsCategory $category)
    {
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        return $this->renderThemeView(
            'Theme:News:CategoryList/subscription_info.html.twig', array(
                'category'      => $category,
                'is_subscribed' => $is_subscribed,
            )
        );
    }

    /**
     * @Tag(name="news_post_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "post": null
     *      },
     *      allowed_types={
     *          "post":{"Application\DeskPRO\Entity\News","int","string"}
     *      },
     *      attribute_expressions={
     *          "post": "service('data.news').getPost(options['post'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, News $post)
    {
        $comments = $this->getNewsDataService()->getPostComments($post, $this->getUser());

        return $this->renderThemeView('Theme:News:PostView/comments.html.twig', array(
            'post'     => $post,
            'comments' => $comments,
        ));
    }

    /**
     * @Tag(name="news_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "show_pagination": true,
     *          "page": 1,
     *          "count": 10
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.news').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function pagerAction(TagRequest $tag_request, array $options, NewsCategory $category = null)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $pager = $this->getNewsDataService()->getNewsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            'Theme:Common:pager.html.twig',
            array(
                'pager' => $pager,
            )
        );
    }

    /**
     * @Tag(name="news_post_ratings", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"post": null},
     *      allowed_types={
     *          "post": {"Application\DeskPRO\Entity\News", "int", "string"}
     *      },
     *      attribute_expressions={
     *          "post": "service('data.news').getPost(options['post'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function ratingsAction(TagRequest $tag_request, array $options, News $post)
    {
        $rating = $this->getRatingsHelper()->getPersonRating($post, $this->getUser());

        return $this->renderThemeView(
            'Theme:News:PostView/ratings.html.twig',
            array(
                'post'   => $post,
                'rating' => $rating,
            )
        );
    }
}
