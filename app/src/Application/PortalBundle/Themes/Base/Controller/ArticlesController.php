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
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Annotation\Tag;

class ArticlesController extends AbstractController
{
    /**
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function indexAction()
    {
        return $this->render('Theme:Articles:index.html.twig');
    }


    /**
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function browseAction(ArticleCategory $category, Request $request)
    {
        return $this->render('Theme:Articles:browse.html.twig', array(
                'cat' => $category,
                'page' => $request->get('page', 1)
            )
        );
    }

    /**
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function viewAction(Article $article)
    {
        return $this->render('Theme:Articles:view.html.twig', array('article' => $article));
    }

    /**
     * @Tag(name="kb_articles_forcat", default_options={"style":"forcat"})
     * @Tag(name="kb_articles_small", default_options={"style":"small"})
     * @Tag(name="kb_articles_xsmall", default_options={"style":"xsmall"})
     * @Tag(name="kb_articles_simple", default_options={"style":"simple"})
     *
     * @TagOptions({
     *      "defaults": {
     *          "show_pagination": false,
     *          "page": 1,
     *          "max_per_page": 10,
     *          "style": "small",
     *          "include_subcategories": false,
     *          "count": 10,
     *          "labelled": "",
     *          "sort": "date_published desc"
     *      },
     *      "allowedValues": {
     *          "style": {"forcat", "small", "xsmall", "simple"}
     *      },
     *      "required": {"category"}
     * })
     */
    public function listAction(TagRequest $request)
    {
        $options = $request->getTagOptions();

        if (!$options['category'] instanceof ArticleCategory) {
            $options['category'] = $this->getArticleCategoryRepo()->find($options['category']);
        }
        $pager = $this->getArticlesDataService()->getArticlesPager($options['category'], $options['page'], $options['max_per_page']);

        return $this->render(
            sprintf('Theme:Articles:list_%s.html.twig', $options['style']),
            array(
                'show_pagination' => $options['show_pagination'],
                'cat' => $options['category'],
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="knowledgebase", default_options={"style":"home"})
     * @Tag(name="knowledgebase_compact", default_options={"style":"summary"})
     * @Tag(name="knowledgebase_list", default_options={"style":"expander"})
     * @Tag(name="kb_cats_list", default_options={"style":"small"})
     *
     * @TagOptions({
     *      "defaults": {
     *          "style": "small",
     *          "parent": null,
     *          "articles": {
     *              "include_subcategories": false
     *          }
     *      },
     *      "allowedValues": {
     *          "style": {"expander", "home", "small", "summary"}
     *      }
     * })
     */
    public function categoriesAction(TagRequest $request, array $options)
    {
        /** @var \Application\DeskPRO\EntityRepository\ArticleCategory $categories */
        if ($options['parent']) {
            $categories = $this->getArticleCategoryRepo()->findBy(array('parent' => $options['parent']));
        } else {
            $categories = $this->getArticleCategoryRepo()->findAll();
        }

        return $this->render(
            sprintf('Theme:Articles:cats_%s.html.twig', $options['style']),
            array(
                'cats' => $categories,
                'articles_options' => $options['articles']
            )
        );
    }

    /**
     * @Tag(name="kb_category_breadcrumbs")
     *
     * @TagOptions({
     *      "required": {"category"},
     *      "allowedTypes": {
     *          "category": "Application\DeskPRO\Entity\ArticleCategory"
     *      }
     * })
     */
    public function breadcrumbsAction(TagRequest $request, array $options)
    {
        return $this->render('Theme:Articles:breadcrumbs.html.twig', array(
            'category' => $options['category']
        ));
    }

    /**
     * @return \Application\AppBundle\DataService\ArticlesDataService
     */
    protected function getArticlesDataService()
    {
        return $this->get('data.articles');
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\Article
     */
    protected function getArticlesRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:Article');
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\ArticleCategory
     */
    protected function getArticleCategoryRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:ArticleCategory');
    }
}
