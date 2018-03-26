<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\Notifications\NewCommentNotification;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\Form\Handler\CommentFormHandler;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Component\Pdf\PdfRendererInterface;
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
     *
     * @param Request $request
     * @param $_format
     *
     * @return Response
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getCurrentPerson();

        // RSS

        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                null,
                $page,
                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:News:feed.rss.twig', [
                'page_title' => $this->createPageTitle()->news(),
                'pager'      => $pager,
                'category'   => null,
            ]);
        }
        $rssLink = $this->generateUrl(
            'portal_news',
            ['_format' => 'rss']
        );

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNews();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.news_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('news', $this->getUser());
        }

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:News:index.html.twig',
            [
                'page'          => $page,
                'count'         => $this->getBrandSetting('portal.per_page_content'),
                'page_title'    => $this->createPageTitle()->news(),
                'breadcrumbs'   => $breadcrumbs,
                'rss_link'      => $rssLink,
                'is_subscribed' => $isSubscribed,
            ]
        );
    }

    /**
     * @Route("/news/{slug}.{_format}", name="portal_news_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/news/{slug}", name="user_news")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS_CATEGORY', category)")
     * @PageHttpCache()
     *
     * @param Request      $request
     * @param NewsCategory $category
     * @param $_format
     *
     * @return Response
     */
    public function browseAction(Request $request, NewsCategory $category, $_format)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getCurrentPerson();

        // RSS

        if ('rss' === $_format) {
            $pager = $this->getNewsDataService()->getNewsPager(
                $category,
                $page,
                $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:News:feed.rss.twig', [
                'pager'      => $pager,
                'category'   => $category,
                'page_title' => $this->createPageTitle()->news($category),
            ]);
        }
        $rssLink = $this->generateUrl(
            'portal_news_browse',
            ['slug' => $category->getSlug(), '_format' => 'rss']
        );

        // BREADCRUMBS

        if ($category) {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsCategory($category);
        } else {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNews();
        }

        // SUBSCRIPTIONS

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORY, $category)
        ) {
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        // PAGER

        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getNewsDataService()->getNewsPager($category, $page, $count, $person);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:News:browse.html.twig',
            [
                'category'      => $category,
                'is_subscribed' => $isSubscribed,
                'pager'         => $pager,
                'page'          => $page,
                'count'         => $count,
                'page_title'    => $this->createPageTitle()->news($category),
                'breadcrumbs'   => $breadcrumbs,
                'rss_link'      => $rssLink,
            ]
        );
    }

    /**
     * @Route("/news/posts/{slug}", name="portal_news_view")
     * @Route("/news/posts/{slug}", name="user_news_view")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS', post)")
     * @PageHttpCache(content="post")

     *
     * @param Request $request
     * @param News    $post
     * @param int     $visitor_id
     *
     * @return Response
     */
    public function viewAction(Request $request, News $post, $visitor_id)
    {

        // COMMENT FORM

        $newCommentForm = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_NEWS, $post)) {
            /** @var CommentFormHandler $formHandler */
            $formHandler = $this->get('form_handler.comment');
            $comment     = new NewsComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $formResult     = $formHandler->handle($newCommentForm, $request, $post, $comment);
            if ($formResult) {
                $notify = new NewCommentNotification($comment);
                $notify->send();
            }
            if ($formResult instanceof Response) {
                return $formResult;
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsPost($post);

        // RATINGS

        $rating = $this->findContentRating($post, $visitor_id);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($post);

        // SUBSCRIPTIONS

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS, $post)
        ) {
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedContent($post, $this->getUser());
        }

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // REGISTERED PAGE VIEW LOG
        if ($person = $this->getUser()) {
            $this->container->get('content.page_view')->pageView($person, PageViewLog::TYPE_NEWS, $post->getId());
        }

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:News:view.html.twig',
            [
                'post'               => $post,
                'is_subscribed'      => $isSubscribed,
                'rating'             => $rating,
                'category'           => $post->getCategory(),
                'content_id'         => $post->getId(),
                'content_type'       => News::CONTENT_TYPE,
                'new_comment_form'   => $newCommentForm ? $newCommentForm->createView() : null,
                'page_title'         => $this->createPageTitle()->news($post),
                'breadcrumbs'        => $breadcrumbs,
                'show_rating_counts' => $showRatingCounts,
                'rating_counts'      => $ratingCounts,
                'lockout'            => $check->isLockoutRecommended(),
                'lockout_time'       => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * Need to force a redirect here to support old permalinks!
     *
     * @Route("/news/view/{slug}", name="portal_news_view_LEGACY")
     *
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewLEGACYAction($slug)
    {
        /** @var News $post */
        $post = $this->getRepo(News::class)->getBySlug($slug);

        if (!$post) {
            throw $this->createNotFoundException('could not find new post for slug "'.$slug.'"');
        }

        // RENDER THEME

        return $this->redirect(
            $this->generateUrl('portal_news_view', [
                'slug' => $post->getSlug(),
            ]),
            301
        );
    }

    /**
     * @Route("/news/posts/{slug}/vote-up", name="portal_news_post_vote_up", defaults={"up_or_down":"up"})
     * @Route("/news/posts/{slug}/vote-down", name="portal_news_post_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('RATE_NEWS', post)")
     * @AutoPostOnGetRequest()
     *
     * @param News $post
     * @param      $visitor_id
     * @param      $up_or_down
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

        return $this->redirectToRoute('portal_news_view', ['slug' => $post->getSlug()]);
    }

    /**
     * @Route("/news/posts/{slug}/toggle-subscription", name="portal_news_post_toggle_subscription")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS', post)")
     * @AutoPostOnGetRequest()
     *
     * @param News $post
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newsSubscriptionAction(News $post)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedContent($post, $person)) {
            $subscriptionsHelper->unsubscribeFromContent($post, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToContent($post, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_subscribe'));
        }

        return $this->redirectToRoute('portal_news_view', ['slug' => $post->getSlug()]);
    }

    /**
     * @Route("/news/category/toggle-subscription/{slug}", name="portal_news_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('SUBSCRIBE_NEWS_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     *
     * @param NewsCategory $category
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newsCategorySubscriptionAction(NewsCategory $category)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedCategory($category, $person)) {
            $subscriptionsHelper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_news_browse', ['slug' => $category->getSlug()]);
    }

    /**
     * @Route("/news/root/toggle-subscription", name="portal_news_root_category_toggle_subscription")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_NEWS')")
     * @AutoPostOnGetRequest()
     */
    public function newsRootCategorySubscriptionAction()
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedRootCategory('news', $person)) {
            $subscriptionsHelper->unsubscribeFromRootCategory('news', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('news', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.news_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_news');
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

    /**
     * @Route("/news/posts/pdf/{slug}", name="portal_news_pdf")
     * @ParamConverter(name="post", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS', post)")
     *
     * @param News $post
     * @param int  $visitor_id
     *
     * @return Response
     */
    public function pdfAction(News $post, $visitor_id)
    {
        /** @var PdfRendererInterface $pdfRenderer */
        $pdfRenderer = $this->get('pdf_renderer');

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsPost($post);

        // RATING

        $rating = $this->findContentRating($post, $visitor_id);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($post);

        $contentHtml = $this->renderThemeView(
            'Theme:News:pdf.html.twig',
            [
                'post'               => $post,
                'rating'             => $rating,
                'category'           => $post->getCategory(),
                'breadcrumbs'        => $breadcrumbs,
                'content_id'         => $post->getId(),
                'content_type'       => News::CONTENT_TYPE,
                'page_title'         => $this->createPageTitle()->news($post),
                'show_rating_counts' => $showRatingCounts,
            ]
        );

        return $pdfRenderer->generateFile($contentHtml->getContent(), $post->getTitle().'.pdf');
    }
}
