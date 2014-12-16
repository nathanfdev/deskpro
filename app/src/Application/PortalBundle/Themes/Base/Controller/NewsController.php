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
use Application\PortalBundle\Controller\AbstractController;
use Application\PortalBundle\Request\TagRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Application\PortalBundle\Annotation\TagOptions;
use Application\PortalBundle\Annotation\Tag;

class NewsController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $qb = $this->getNewsRepo()->createQueryBuilder('n');
        $pager = new Pagerfanta(new DoctrineCollectionAdapter(new ArrayCollection($qb->select('n')->getQuery()->execute())));
        $pager->setCurrentPage($request->get('page', 1));
        $pager->setMaxPerPage(10);

        return $this->render('Theme:News:index.html.twig',
            array(
                'pager' => $pager,
                'news_articles' => $pager->getCurrentPageResults()
            )
        );
    }


    /**
     * @ParamConverter(name="category", converter="deskpro_slug")
     */
    public function browseAction(Request $request, NewsCategory $category)
    {
        if (!$category) {
            throw $this->createNotFoundException('news category "' . $slug . '" not found');
        }

        $qb    = $this->getNewsRepo()->createQueryBuilder('n');
        $qb->andWhere('n.category = :cat')->setParameter('cat', $category);
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setCurrentPage($request->get('page', 1));
        $pager->setMaxPerPage(3);

        return $this->render(
            'Theme:News:browse.html.twig',
            array(
                'cat'           => $category,
                'pager'         => $pager,
                'news_articles' => $pager->getCurrentPageResults()
            )
        );
    }

    /**
     * @ParamConverter(name="news", converter="deskpro_slug")
     */
    public function viewAction(News $news)
    {
        return $this->render(
            'Theme:News:view.html.twig',
            array(
                'cat'     => $news->category,
                'article' => $news
            )
        );
    }


    /**
     * @Tag(name="news_cats_list", default_options={"style":"small"})
     * @Tag(name="news_cats_dropdown", default_options={"style":"dropdown"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "small",
     *          "parent": null
     *      },
     *      allowed_values={
     *          "style": {"small", "dropdown"}
     *      }
     * )
     */
    public function catsAction(TagRequest $tag_request, array $options)
    {
        if ($category = $options['parent']) {
            if (!$category instanceof NewsCategory) {
                $category = $this->getNewsCategoriesRepo()->find($category);
            }
            $categories = $category->children;
        } else {
            $categories = $this->getNewsCategoriesRepo()->findBy(array('parent' => $category));
        }

        return $this->render(
            sprintf('Theme:News:cats_%s.html.twig', $options['style']),
            array(
                'cat'        => $category,
                'child_cats' => $categories
            )
        );
    }


    /**
     * @Tag(name="news")
     * @Tag(name="news_posts", default_options={"style":"posts"})
     * @Tag(name="news_posts_list", default_options={"style":"small"})
     *
     * @TagOptions(
     *      defaults={
     *          "style": "small",
     *          "count": 5
     *      },
     *      allowed_values={
     *          "style": {"posts", "small"}
     *      }
     * )
     */
    public function listAction(Request $request, array $options)
    {
        $news  = $this->getNewsRepo()->getNewest($options['count']);
        $total = $this->getNewsRepo()->countPublished();

        return $this->render(
            sprintf('Theme:News:list_%s.html.twig', $options['style']),
            array(
                'news_count_total' => $total,
                'news_articles'    => $news
            )
        );
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
