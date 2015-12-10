<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Themes\Base\Controller;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\PortalBundle\Annotation\Tag;
use DeskPRO\Bundle\PortalBundle\Annotation\TagOptions;
use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\TagHttpCache;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class ArticlesController extends AbstractController
{
    /**
     * @Tag(name="kb", esi=true, always_guest_inline=true)
     * @Tag(name="kb_cats", esi=true, always_guest_inline=true)
     * @Tag(name="kb_cats_expander", default_options={"style":"expander"}, esi=true, always_guest_inline=true)
     * @Tag(name="kb_cats_simple", default_options={"style":"simple"}, esi=true, always_guest_inline=true)
     * @TagHttpCache
     *
     * @TagOptions(
     *      defaults={
     *          "style": "browse",
     *          "category": null,
     *          "articles_options": {}
     *      },
     *      allowed_values={
     *          "style": {"expander", "browse", "list"}
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
        $person = $this->getCurrentPerson();

        if ($category) {
            $permissions_bag = $this->getPermissionBag($person);
            if (!$permissions_bag->hasContentCategoryAccess($category)) {
                return new Response(''); // no access to the category will exclude children
            }
        }

        $category_children = $this->getArticlesDataService()->getCategoryChildren($category, $person);

        if (empty($category_children)) {
            return new Response(''); // nothing to display here
        }

        // the cat list might want details on the total # of articles, and we need a pager because it
        // takes into account permissions
        $category_pager           = $this->getArticlesDataService()->getArticlesPager($category, 1, 1, $person);
        $category_children_pagers = [];
        foreach ($category_children as $child_cat) {
            $category_children_pagers[$child_cat->getId()] = $this->getArticlesDataService()->getArticlesPager($child_cat, 1, 1, $person);
        }

        return $this->renderThemeView(
            sprintf('Theme:Articles:CategoryList/%s.html.twig', $options['style']),
            array(
                'category'                 => $category,
                'category_pager'           => $category_pager,
                'category_children'        => $category_children,
                'category_children_pagers' => $category_children_pagers,
                'articles_options'         => $options['articles_options'],
            )
        );
    }

    /**
     * @Tag(name="kb_list_detail", default_options={"style":"detail"})
     * @Tag(name="kb_list_simple", default_options={"style":"simple"})
     *
     * @TagOptions(
     *      defaults={
     *          "category":null,
     *          "style": "simple",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": false
     *      },
     *      inherit_from={"articles_options"},
     *      allowed_values={
     *          "style": {"detail", "simple"}
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
        $person = $this->getCurrentPerson();
        $pager  = $this->getArticlesDataService()->getArticlesPager($category, $options['page'], $options['count'], $person);

        return $this->renderThemeView(
            sprintf('Theme:Articles:ArticleList/%s.html.twig', $options['style']),
            array(
                'pager'              => $pager,
                'category'           => $category,
                'show_category_link' => $options['show_category_link'],
            )
        );
    }

    /**
     * @Tag(name="kb_article_comments", esi=true)
     *
     * @TagOptions(
     *      defaults={
     *          "article": null
     *      },
     *      allowed_types={
     *          "article":{"Application\DeskPRO\Entity\Article","int","string"}
     *      },
     *      attribute_expressions={
     *          "article": "service('data.articles').getArticle(options['article'])"
     *      }
     * )
     *
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function commentsAction(TagRequest $tag_request, array $options, Article $article)
    {
        $comments = $this->getArticlesDataService()->getArticleComments($article, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', array(
            'comments' => $comments,
        ));
    }
}
