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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class NewsController extends AbstractController
{
    /**
     * @Tag(name="news")
     * @Tag(name="news_list", default_options={"style":"list"})
     * @Tag(name="news_dropdown", default_options={"style":"dropdown"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "pretty",
     *          "category": null
     *      },
     *      allowed_values={
     *          "style": {"list", "dropdown"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getNewsDataService()->getCategory($options['category']);
        $category_children = $this->getNewsDataService()->getCategoryChildren($category);

        return $this->renderThemeView(
            sprintf('Theme:News:Tag/%s.html.twig', $options['style']),
            array(
                'category' => $category,
                'category_children' => $category_children
            )
        );
    }


    /**
     * @Tag(name="news_posts")
     * @Tag(name="news_posts_list", default_options={"style":"list"})
     * @Tag(name="news_posts_pretty", default_options={"style":"pretty"})
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "pretty",
     *          "page": 1,
     *          "count": 2,
     *          "show_category_link": true
     *      },
     *      allowed_values={
     *          "style": {"pretty", "list"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getNewsDataService()->getCategory($options['category']);
        $pager = $this->getNewsDataService()->getNewsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            sprintf('Theme:News:Tag/posts_%s.html.twig', $options['style']),
            array(
                'pager' => $pager,
                'show_category_link' => $options['show_category_link'],
                'category' => $category
            )
        );
    }

    /**
     * @Tag(name="news_comments")
     * @TagOptions(
     *      defaults={
     *          "post": null
     *      },
     *      allowed_types={
     *          "post":{"Application\DeskPRO\Entity\News","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function commentsAction(TagRequest $tag_request, array $options)
    {
        $post = $this->getNewsDataService()->getPost($options['post']);
        $comments = $this->getNewsDataService()->getPostComments($post, $this->getUser());

        return $this->renderThemeView('Theme:News:Tag/comments.html.twig', array(
            'post' => $post,
            'comments' => $comments
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
     *          "count": 5
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $category = $this->getNewsDataService()->getCategory($options['category']);
        $pager = $this->getNewsDataService()->getNewsPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            'Theme:Common:pager.html.twig',
            array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="news_breadcrumbs")
     *
     * @TagOptions(
     *      defaults={"category": null, "post": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\NewsCategory", "int", "string", "null"},
     *          "post": {"Application\DeskPRO\Entity\News", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function breadcrumbsAction(TagRequest $request, array $options)
    {
        $category = $this->getNewsDataService()->getCategory($options['category']);
        $post = $this->getNewsDataService()->getPost($options['post']);

        return $this->renderThemeView(
            'Theme:News:Tag/breadcrumbs.html.twig',
            array(
                'category' => $category,
                'post' => $post
            )
        );
    }

    /**
     * @Tag(name="news_ratings")
     *
     * @TagOptions(
     *      defaults={"rating": null, "post": null},
     *      allowed_types={
     *          "rating": {"Application\DeskPRO\Entity\Rating", "int", "string", "null"},
     *          "post": {"Application\DeskPRO\Entity\News", "int", "string", "null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function ratingsAction(TagRequest $tag_request, array $options)
    {
        $rating = $this->getRatingDataService()->getRating($options['rating']);
        $post = $this->getNewsDataService()->getPost($options['post']);

        return $this->renderThemeView(
            'Theme:News:Tag/ratings.html.twig',
            array(
                'rating' => $rating,
                'post' => $post
            )
        );
    }

    /**
     * @Tag(name="news_subscriptions_category")
     *
     * @TagOptions(
     *      defaults={"category": null, "is_subscribed": false},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\NewsCategory", "int", "string", "null"},
     *          "is_subscribed": {"int", "string", "bool"},
     *      }
     * )
     *
     * @Security("is_granted('USE_NEWS')")
     */
    public function subscriptionsCategoryAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getNewsDataService()->getCategory($options['category']);
        $is_subscribed = (bool)$options['is_subscribed'];

        return $this->renderThemeView(
            'Theme:News:Tag/subscriptions_category.html.twig', array(
                'category' => $category,
                'is_subscribed' => $is_subscribed
            )
        );
    }
}
