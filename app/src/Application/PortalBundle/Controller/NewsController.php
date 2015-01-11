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

namespace Application\PortalBundle\Controller;


use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\PortalBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class NewsController extends AbstractController
{
    /**
     * @Route("/news", name="portal_news")
     * @Security("is_granted('USE_NEWS')")
     */
    public function indexAction(Request $request)
    {
        return $this->renderThemeView(
            'Theme:News:index.html.twig',
            array(
                'page' => $request->query->get('page', 1),
                'count' => 2
            )
        );
    }

    /**
     * @Route("/news/{slug}", name="portal_news_browse")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS')")
     */
    public function browseAction(Request $request, NewsCategory $category)
    {
        return $this->renderThemeView(
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
     * @Route("/news/posts/{slug}", name="portal_news_view")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS')")
     */
    public function viewAction(Request $request, News $post)
    {
        $related = $this->getNewsDataService()->getRelatedPosts($post);
        $rating = $this->getRatingsHelper()->getPersonRating($post, $this->getUser());

        return $this->renderThemeView(
            'Theme:News:view.html.twig',
            array(
                'category' => $post->category,
                'post' => $post,
                'related_news' => $related,
                'rating' => $rating
            )
        );
    }

    /**
     * Need to force a redirect here to support old permalinks!
     *
     * @Route("/news/view/{slug}", name="portal_news_view_LEGACY")
     */
    public function viewLEGACYAction(Request $request, $slug)
    {
        $post = $this->getRepo('DeskPRO:News')->getBySlug($slug);

        if (!$post) {
            throw $this->createNotFoundException('could not find new post for slug "'.$slug.'"');
        }

        return $this->redirect(
            $this->generateUrl('portal_news_view', array(
                'slug' => $post->getSlug()
            )),
            301
        );
    }

    /**
     * @Route("/news/posts/{slug}/vote-up", name="portal_news_post_vote_up", defaults={"up_or_down":"up"})
     * @Route("/news/posts/{slug}/vote-down", name="portal_news_post_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('RATE_NEWS')")
     */
    public function newsRateAction(News $post, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($post, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($post, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_news_view', array('slug' => $post->getSlug()));
    }

    /**
     * @return \Application\AppBundle\DataService\NewsDataService
     */
    public function getNewsDataService()
    {
        return $this->get('data.news');
    }
}
