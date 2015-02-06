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


use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\HttpCache\Configuration\TagHttpCache;

class ArticlesController extends AbstractController
{
    /**
     * @Tag(name="knowledgebase", esi=true)
     * @Tag(name="knowledgebase_compact", default_options={"style":"compact"}, esi=true)
     * @Tag(name="knowledgebase_expander", default_options={"style":"expander"}, esi=true)
     * @Tag(name="knowledgebase_list", default_options={"style":"list"}, esi=true)
     * @Tag(name="knowledgebase_comma_list", default_options={"style":"comma_list"}, esi=true)
     * @TagHttpCache
     *
     * @TagOptions(
     *      defaults={
     *          "style": "home",
     *          "category": null,
     *          "articles_options": {}
     *      },
     *      allowed_values={
     *          "style": {"expander", "compact", "home", "list", "comma_list"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.articles').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function categoriesAction(TagRequest $tag_request, array $options, ArticleCategory $category = null)
    {
        $category_children = $this->getArticlesDataService()->getCategoryChildren($category);

        return $this->renderThemeView(
            sprintf('Theme:Articles:Tag/%s.html.twig', $options['style']),
            array(
                'category' => $category,
                'category_children' => $category_children,
                'articles_options' => $options['articles_options']
            )
        );
    }

    /**
     * @Tag(name="knowledgebase_articles")
     * @Tag(name="knowledgebase_articles_forcat", default_options={"style":"forcat"})
     * @Tag(name="knowledgebase_articles_list", default_options={"style":"list"})
     * @Tag(name="knowledgebase_articles_small", default_options={"style":"small"})
     * @Tag(name="knowledgebase_articles_simple", default_options={"style":"simple"})
     *
     * @TagOptions(
     *      defaults={
     *          "category":null,
     *          "style": "small",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": true
     *      },
     *      allowed_values={
     *          "style": {"forcat", "list", "small", "simple"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\ArticleCategory","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.articles').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function listAction(TagRequest $tag_request, array $options, ArticleCategory $category = null)
    {
        $pager = $this->getArticlesDataService()->getArticlesPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            sprintf('Theme:Articles:Tag/articles_%s.html.twig', $options['style']),
            array(
                'pager' => $pager,
                'category' => $category,
                'show_category_link' => $options['show_category_link']
            )
        );
    }

    /**
     * @Tag(name="article", esi=true)
     * @TagHttpCache(content="article")
     *
     * @TagOptions(
     *      required={"article"},
     *      allowed_types={
     *          "article": {"Application\DeskPRO\Entity\Article", "int", "string", "null"}
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])"
     *      }
     * )
     */
    public function articleAction(TagRequest $tag_request, array $options, Article $article = null)
    {
        return $this->renderThemeView(
            'Theme:Articles:Tag/article.html.twig',
            array(
                'article' => $article
            )
        );
    }

    /**
     * @Tag(name="article_subscription", esi=true, always_guest_inline=true)
     * @Tag(name="article_subscription_info", default_options={"style":"info"}, esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      required={"article"},
     *      defaults={"style":"link"},
     *      allowed_types={
     *          "article": {"Application\DeskPRO\Entity\Article", "int", "string", "null"}
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])"
     *      }
     * )
     */
    public function articleSubscriptionAction(TagRequest $tag_request, array $options, Article $article = null)
    {
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($article, $this->getUser());
        }

        return $this->renderThemeView(
            sprintf('Theme:Articles:Tag/article_subscription_%s.html.twig', $options['style']),
            array(
                'article' => $article,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Tag(name="knowledgebase_category_subscription", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"category": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\ArticleCategory", "int", "string", "null"}
     *      },
     *      attribute_expressions={
     *          "category": "service('data.articles').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function categorySubscriptionAction(TagRequest $tag_request, array $options, ArticleCategory $category = null)
    {
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        return $this->renderThemeView(
            'Theme:Articles:Tag/subscription_category.html.twig', array(
                'category' => $category,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Tag(name="article_comments")
     *
     * @TagOptions(
     *      defaults={
     *          "article": null
     *      },
     *      allowed_types={
     *          "article":{"Application\DeskPRO\Entity\Article","int","string","null"}
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, Article $article = null)
    {
        $comments = $this->getArticlesDataService()->getArticleComments($article, $this->getUser());

        return $this->renderThemeView('Theme:Articles:Tag/comments.html.twig', array(
            'article' => $article,
            'comments' => $comments
        ));
    }

    /**
     * @Tag(name="knowledgebase_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "show_pagination": true,
     *          "page": 1,
     *          "count": 10
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\ArticleCategory","int","string","null"}
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $category = $this->getArticlesDataService()->getCategory($options['category']);
        $pager = $this->getArticlesDataService()->getArticlesPager($category, $options['page'], $options['count']);

        return $this->renderThemeView(
            'Theme:Common:pager.html.twig', array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="knowledgebase_breadcrumbs", esi=true)
     * @TagHttpCache
     *
     * @TagOptions(
     *      defaults={"category": null, "article": null},
     *      allowed_types={
     *          "category": {"Application\DeskPRO\Entity\ArticleCategory", "int", "string", "null"},
     *          "article": {"Application\DeskPRO\Entity\Article", "int", "string", "null"},
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])",
     *          "category": "service('data.articles').getCategory(options['category'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function breadcrumbsAction(TagRequest $tag_request, array $options, Article $article = null, ArticleCategory $category = null)
    {
        $category = $this->getArticlesDataService()->getCategory($options['category']);

        return $this->renderThemeView(
            'Theme:Articles:Tag/breadcrumbs.html.twig', array(
                'category' => $category,
                'article' => $article
            )
        );
    }

    /**
     * @Tag(name="article_ratings", esi=true, always_guest_inline=true)
     *
     * @TagOptions(
     *      defaults={"article": null},
     *      allowed_types={
     *          "article": {"Application\DeskPRO\Entity\Article", "int", "string", "null"}
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function ratingsAction(TagRequest $tag_request, array $options, Article $article = null)
    {
        $rating = $this->getRatingsHelper()->getPersonRating($article, $this->getUser());

        return $this->renderThemeView(
            'Theme:Articles:Tag/ratings.html.twig',
            array(
                'rating' => $rating,
                'article' => $article
            )
        );
    }
}
