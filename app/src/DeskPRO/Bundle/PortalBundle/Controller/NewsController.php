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
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends AbstractController
{
    /**
     * @Route("/news.{_format}", name="portal_news", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/news", name="user_news_home")
     * @Security("is_granted('USE_NEWS')")
     * @PageHttpCache()
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->query->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                null,
                $page,
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:News:feed.rss.twig', array(
                'page_title' => $this->createPageTitle()->news(),
                'pager'      => $pager,
                'category'   => null,
            ));
        }
        $rss_link = $this->generateUrl(
            'portal_news',
            array('_format' => 'rss')
        );

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNews();

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:index.html.twig',
            array(
                'page'        => $page,
                'count'       => $this->getBrandSetting('portal.per_page_content'),
                'page_title'  => $this->createPageTitle()->news(),
                'breadcrumbs' => $breadcrumbs,
                'rss_link'    => $rss_link,
            )
        );
    }

    /**
     * @Route("/news/{slug}.{_format}", name="portal_news_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/news/{slug}", name="user_news")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS_CATEGORY', category)")
     * @PageHttpCache()
     */
    public function browseAction(Request $request, NewsCategory $category, $_format)
    {
        $page   = $request->query->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                $category,
                $page,
                $request->query->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:News:feed.rss.twig', array(
                'pager'      => $pager,
                'category'   => $category,
                'page_title' => $this->createPageTitle()->news($category),
            ));
        }
        $rss_link = $this->generateUrl(
            'portal_news_browse',
            array('slug' => $category->getSlug(), '_format' => 'rss')
        );

        //
        // BREADCRUMBS
        //
        if ($category) {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsCategory($category);
        } else {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNews();
        }

        //
        // SUBSCRIPTIONS
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORY, $category)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        //
        // PAGER
        //
        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getNewsDataService()->getNewsPager($category, $page, $count, $person);

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:browse.html.twig',
            array(
                'category'      => $category,
                'is_subscribed' => $is_subscribed,
                'pager'         => $pager,
                'page'          => $page,
                'count'         => $count,
                'page_title'    => $this->createPageTitle()->news($category),
                'breadcrumbs'   => $breadcrumbs,
                'rss_link'      => $rss_link,
            )
        );
    }

    /**
     * @Route("/news/posts/{slug}", name="portal_news_view")
     * @Route("/news/posts/{slug}", name="user_news_view")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS', post)")
     * @PageHttpCache(content="post")
     */
    public function viewAction(Request $request, News $post, $visitor_id)
    {
        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_NEWS, $post)) {
            $form_handler = $this->get('form_handler.comment');
            $comment      = new NewsComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $new_comment_form = $form_handler->createForm($comment, $request);
            $form_result      = $form_handler->handle($new_comment_form, $request, $post, $comment);
            if ($form_result instanceof Response) {
                return $form_result;
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsPost($post);

        //
        // RATINGS
        //
        $rating = $this->findContentRating($post, $visitor_id);

        //
        // NUM RATINGS
        //
        list($show_rating_counts, $rating_counts) = $this->determineRatingCounts($post);

        //
        // SUBSCRIPTIONS
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS, $post)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($post, $this->getUser());
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:News:view.html.twig',
            array(
                'post'               => $post,
                'is_subscribed'      => $is_subscribed,
                'rating'             => $rating,
                'category'           => $post->getCategory(),
                'content_id'         => $post->getId(),
                'content_type'       => News::CONTENT_TYPE,
                'new_comment_form'   => $new_comment_form ? $new_comment_form->createView() : null,
                'page_title'         => $this->createPageTitle()->news($post),
                'breadcrumbs'        => $breadcrumbs,
                'show_rating_counts' => $show_rating_counts,
                'rating_counts'      => $rating_counts,
            )
        );
    }

    /**
     * Need to force a redirect here to support old permalinks!
     *
     * @Route("/news/view/{slug}", name="portal_news_view_LEGACY")
     * @PageHttpCache(content="post")
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
                'slug' => $post->getSlug(),
            )),
            301
        );
    }

    /**
     * @Route("/news/posts/{slug}/vote-up", name="portal_news_post_vote_up", defaults={"up_or_down":"up"})
     * @Route("/news/posts/{slug}/vote-down", name="portal_news_post_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('RATE_NEWS', post)")
     * @AutoPostOnGetRequest()
     */
    public function newsRateAction(News $post, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($post, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($post, $visitor_id, $person);
        }

        $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

        return $this->redirectToRoute('portal_news_view', array('slug' => $post->getSlug()));
    }

    /**
     * @Route("/news/posts/{slug}/toggle-subscription", name="portal_news_post_toggle_subscription")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS', post)")
     * @AutoPostOnGetRequest()
     */
    public function newsSubscriptionAction(News $post)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($post, $person)) {
            $subscriptions_helper->unsubscribeFromContent($post, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToContent($post, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_subscribe'));
        }

        return $this->redirectToRoute('portal_news_view', array('slug' => $post->getSlug()));
    }

    /**
     * @Route("/news/category/toggle-subscription/{slug}", name="portal_news_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     */
    public function newsCategorySubscriptionAction(NewsCategory $category)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedCategory($category, $person)) {
            $subscriptions_helper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_news_browse', array('slug' => $category->getSlug()));
    }

    /**
     * @Route("/news/posts/subscriptions/unsubscribe", name="portal_news_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_NEWS')")
     * @AutoPostOnGetRequest()
     */
    public function newsUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('news', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.news_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }
}
