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
use Application\PortalBundle\Annotation\Tag;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class NewsController extends AbstractController
{
    public function indexAction(Request $request)
    {
        return $this->render('Theme:News:index.html.twig');
    }


    /**
     * @ParamConverter(name="category", converter="deskpro_slug")
     */
    public function browseAction(Request $request, NewsCategory $category)
    {
        return $this->render(
            'Theme:News:browse.html.twig',
            array(
                'category' => $category,
                'page' => $request->query->get('page', 1),
                'count' => 2,
                'show_pagination' => true
            )
        );
    }

    /**
     * @ParamConverter(name="news", converter="deskpro_slug")
     */
    public function viewAction(Request $request, News $news)
    {
        return $this->render(
            'Theme:News:view.html.twig',
            array(
                'category' => $news->category,
                'post' => $news
            )
        );
    }


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
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","null"}
     *      }
     * )
     */
    public function categoriesAction(TagRequest $tag_request, array $options)
    {
        $category = $options['category'];
        $category_children = $this->getNewsDataService()->getCategoryChildren($category);

        return $this->render(
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
     *          "count": 5,
     *          "show_category_link": true
     *      },
     *      allowed_values={
     *          "style": {"pretty", "list"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","null"}
     *      }
     * )
     */
    public function listAction(TagRequest $tag_request, array $options)
    {
        $pager = $this->getNewsDataService()->getNewsPager($options['category'], $options['page'], $options['count']);

        return $this->render(
            sprintf('Theme:News:Tag/posts_%s.html.twig', $options['style']),
            array(
                'pager' => $pager,
                'show_category_link' => $options['show_category_link'],
                'category' => $options['category']
            )
        );
    }

    /**
     * @Tag(name="news_pager")
     *
     * @TagOptions(
     *      defaults={
     *          "category": null,
     *          "style": "pretty",
     *          "show_pagination": true,
     *          "page": 1,
     *          "count": 5
     *      },
     *      allowed_values={
     *          "style": {"pretty", "list"}
     *      },
     *      allowed_types={
     *          "category":{"Application\DeskPRO\Entity\NewsCategory","int","null"}
     *      }
     * )
     */
    public function pagerAction(TagRequest $tag_request, array $options)
    {
        if (!$options['show_pagination']) {
            return new Response('');
        }

        $category = $this->getNewsDataService()->getCategory($options['category']);
        $pager = $this->getNewsDataService()->getNewsPager($category, $options['page'], $options['count']);

        return $this->render('Theme:Portal:pager.html.twig', array(
                'pager' => $pager
            )
        );
    }

    /**
     * @Tag(name="news_breadcrumbs")
     *
     * @TagOptions(
     *      defaults={"category": null},
     *      allowed_types={"category": {"Application\DeskPRO\Entity\NewsCategory", "int", "null"}}
     * )
     */
    public function breadcrumbsAction(TagRequest $request, array $options)
    {
        $category = $this->getNewsDataService()->getCategory($options['category']);

        return $this->render('Theme:News:Tag/breadcrumbs.html.twig', array(
            'category' => $category
        ));
    }


    /**
     * @return \Application\AppBundle\DataService\NewsDataService
     */
    public function getNewsDataService()
    {
        return $this->get('data.news');
    }


    /**
     * @return \Application\DeskPRO\EntityRepository\News
     */
    protected function getNewsRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:News');
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\NewsCategory
     */
    protected function getNewsCategoriesRepo()
    {
        return $this->getDoctrine()->getRepository('DeskPRO:NewsCategory');
    }
}
