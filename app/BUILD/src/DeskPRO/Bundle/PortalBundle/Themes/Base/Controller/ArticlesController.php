<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
     * @Tag(name="kb", esi=true)
     * @Tag(name="kb_cats", esi=true)
     * @Tag(name="kb_cats_expander", default_options={"style":"expander"}, esi=true)
     * @Tag(name="kb_cats_simple", default_options={"style":"simple"}, esi=true)
     * @TagHttpCache
     *
     * @TagOptions(
     *      defaults={
     *          "style": "browse",
     *          "category": null,
     *          "articles_count": 10,
     *          "with_tree": false
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
     *
     * @param TagRequest      $tag_request
     * @param array           $options
     * @param ArticleCategory $category
     *
     * @return Response
     */
    public function categoriesAction(TagRequest $tag_request, array $options, ArticleCategory $category = null)
    {
        $person = $this->getCurrentPerson();

        if ($category) {
            $permissionsBag = $this->getPermissionBag($person);
            if (!$permissionsBag->hasContentCategoryAccess($category)) {
                return new Response(''); // no access to the category will exclude children
            }
        }

        $categoryChildren = $this->getArticlesDataService()->getCategoryChildren($category, $person);

        if (empty($categoryChildren)) {
            return new Response(''); // nothing to display here
        }

        // the cat list might want details on the total # of articles, and we need a pager because it
        // takes into account permissions
        $categoryPager          = $this->getArticlesDataService()->getArticlesPager($category, 1, 1, $person, $options['with_tree']);
        $categoryChildrenPagers = [];
        foreach ($categoryChildren as $childCat) {
            $categoryChildrenPagers[$childCat->getId()] = $this->getArticlesDataService()->getArticlesPager($childCat, 1, 5, $person, $options['with_tree']);
        }

        return $this->renderThemeView(
            sprintf('Theme:Articles:CategoryList/%s.html.twig', $options['style']),
            [
                'category'                 => $category,
                'category_pager'           => $categoryPager,
                'category_children'        => $categoryChildren,
                'category_children_pagers' => $categoryChildrenPagers,
                'articles_count'           => $options['articles_count'],
                'with_tree'                => $options['with_tree'],
            ]
        );
    }

    /**
     * @Tag(name="kb_list_detail", default_options={"style":"detail", "show_pager": true}, allow_route_params=true)
     * @Tag(name="kb_list_simple", default_options={"style":"simple"}, esi=true)
     * @TagHttpCache
     *
     * @TagOptions(
     *      defaults={
     *          "category":null,
     *          "style": "simple",
     *          "page": 1,
     *          "count": 10,
     *          "show_category_link": false,
     *          "show_pager": false,
     *          "with_tree": false
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
     *
     * @param TagRequest           $tag_request
     * @param array                $options
     * @param ArticleCategory|null $category
     *
     * @return Response
     */
    public function listAction(TagRequest $tag_request, array $options, ArticleCategory $category = null)
    {
        $person = $this->getCurrentPerson();
        $pager  = $this->getArticlesDataService()->getArticlesPager(
            $category,
            (int) $options['page'],
            (int) $options['count'],
            $person,
            $options['with_tree']
        );

        return $this->renderThemeView(
            sprintf('Theme:Articles:ArticleList/%s.html.twig', $options['style']),
            [
                'pager'              => $pager,
                'category'           => $category,
                'show_category_link' => $options['show_category_link'],
                'show_pager'         => $options['show_pager'],
            ]
        );
    }

    /**
     * @Tag(name="kb_article_comments")
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
     *
     * @param TagRequest $tag_request
     * @param array      $options
     * @param Article    $article
     *
     * @return Response
     */
    public function commentsAction(TagRequest $tag_request, array $options, Article $article)
    {
        $comments = $this->getArticlesDataService()->getArticleComments($article, $this->getUser());

        return $this->renderThemeView('Theme:Common:comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
