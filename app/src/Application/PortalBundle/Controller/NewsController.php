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


use Application\AuthBundle\Voter\Portal\ContentCommentVoter;
use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use Application\PortalBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class NewsController extends AbstractController
{
    /**
     * @Route("/news.{_format}", name="portal_news", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_NEWS')")
     */
    public function indexAction(Request $request, $_format)
    {
        $page = $request->query->get('page', 1);
        $per_page = $request->query->get('per_page', 10); // TODO: brand setting?


        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                null,
                $page,
                $per_page
            );

            return $this->render('PortalBundle:News:feed.rss.twig', array(
                'pager' => $pager,
                'category' => null
            ));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:index.html.twig',
            array(
                'page' => $page,
                'count' => $per_page
            )
        );
    }

    /**
     * @Route("/news/{slug}.{_format}", name="portal_news_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS_CATEGORY', category)")
     */
    public function browseAction(Request $request, NewsCategory $category, $_format)
    {
        $page = $request->query->get('page', 1);
        $per_page = $request->query->get('per_page', 10); // TODO: brand setting?


        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                $category,
                $page,
                $per_page
            );

            return $this->render('PortalBundle:News:feed.rss.twig', array(
                'pager' => $pager,
                'category' => $category
            ));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:browse.html.twig',
            array(
                'category' => $category,
                'page' => $page,
                'count' => $per_page,
                'show_pagination' => true
            )
        );
    }

    /**
     * @Route("/news/posts/{slug}", name="portal_news_view")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS', post)")
     */
    public function viewAction(Request $request, News $post)
    {
        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_NEWS)) {
            $comment = new NewsComment();
            $comment->setObject($post);
            $new_comment_form = $this->createForm('comment', $comment, array(
                'person' => $this->getUser()
            ));
            $new_comment_form->handleRequest($request);
            if ($new_comment_form->isValid()) {
                $post->addComment($comment);
                $this->getEm()->persist($comment);
                $this->getEm()->flush($comment, $post);

                return $this->redirectToRoute('portal_news_view', array('slug' => $post->getSlug()));
            }
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:view.html.twig',
            array(
                'post' => $post,
                'category' => $post->category,
                'content_id' => $post->getId(),
                'content_type' => News::CONTENT_TYPE,
                'new_comment_form' => $new_comment_form ? $new_comment_form->createView() : null
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


        //
        // RENDER THEME
        //
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
     * @Security("is_granted('USE_NEWS') and is_granted('RATE_NEWS', post)")
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
     * @Route("/news/posts/{slug}/toggle-subscription", name="portal_news_post_toggle_subscription")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS', post)")
     */
    public function newsSubscriptionAction(News $post)
    {
        $person = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($post, $person)) {
            $subscriptions_helper->unsubscribeFromContent($post, $person);
            $this->addFlash('success', 'Successfully unsubscribed from this news article.');
        } else {
            $subscriptions_helper->subscribeToContent($post, $person);
            $this->addFlash('success', 'You have successfully subscribed to this news article. You will be notified when it is updated.');
        }

        return $this->redirectToRoute('portal_news_view', array('slug' => $post->getSlug()));
    }


    /**
     * @Route("/news/category/toggle-subscription/{slug}", name="portal_news_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS_CATEGORIES', category)")
     */
    public function newsCategorySubscriptionAction(NewsCategory $category)
    {
        $person = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedCategory($category, $person)) {
            $subscriptions_helper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', 'Successfully unsubscribed from this category.');
        } else {
            $subscriptions_helper->subscribeToCategory($category, $person);
            $this->addFlash('success', 'You have successfully subscribed to this category. You will be notified when it is updated.');
        }

        return $this->redirectToRoute('portal_news_browse', array('slug' => $category->getSlug()));
    }

    /**
     * @Route("/news/posts/subscriptions/unsubscribe", name="portal_news_unsubscribe_all")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_NEWS')")
     */
    public function newsUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('news', $this->getUser());

        $this->addFlash('success', 'Unsubscribed from all News subscriptions');

        return $this->redirectToRoute('portal_index');
    }
}
