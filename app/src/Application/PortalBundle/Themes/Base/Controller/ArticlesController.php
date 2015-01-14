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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Annotation\Tag;

class ArticlesController extends AbstractController
{
    /**
     * @Tag(name="knowledgebase_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "show_pagination": true,
     *          "page": 1,
     *          "count": 2
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\ArticleCategory","int","string","null"}
     *      }
     * )
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
     * @Tag(name="knowledgebase_breadcrumbs")
     *
     * @TagOptions(
     *      defaults={"category": null},
     *      allowed_types={"category": {"Application\DeskPRO\Entity\ArticleCategory", "int", "string", "null"}}
     * )
     */
    public function breadcrumbsAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getArticlesDataService()->getCategory($options['category']);

        return $this->renderThemeView(
            'Theme:Articles:Tag/breadcrumbs.html.twig', array(
                'category' => $category
            )
        );
    }

    /**
     * @Tag(name="knowledgebase")
     * @Tag(name="knowledgebase_compact", default_options={"style":"compact"})
     * @Tag(name="knowledgebase_expander", default_options={"style":"expander"})
     * @Tag(name="knowledgebase_list", default_options={"style":"list"})
     * @Tag(name="knowledgebase_comma_list", default_options={"style":"comma_list"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "home",
     *          "category": null,
     *          "articles_options": {}
     *      },
     *      allowed_values={
     *          "style": {"expander", "compact", "home", "list", "comma_list"}
     *      }
     * )
     */
    public function categoriesAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getArticlesDataService()->getCategory($options['category']);
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
     *          "count": 2,
     *          "show_category_link": true
     *      },
     *      allowed_values={
     *          "style": {"forcat", "list", "small", "simple"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\ArticleCategory","int","string","null"}
     *      }
     * )
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $category = $this->getArticlesDataService()->getCategory($options['category']);
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
     * @Tag(name="article_comments")
     * @TagOptions(
     *      defaults={
     *          "article": null
     *      },
     *      allowed_types={
     *          "article":{"Application\DeskPRO\Entity\Article","int","string","null"}
     *      }
     * )
     */
    public function commentsAction(TagRequest $tag_request, array $options)
    {
        $article = $this->getArticlesDataService()->getArticle($options['article']);
        $comments = $this->getArticlesDataService()->getArticleComments($article, $this->getUser());

        return $this->renderThemeView('Theme:Articles:Tag/comments.html.twig', array(
            'article' => $article,
            'comments' => $comments
        ));
    }
}
