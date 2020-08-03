<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
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
use DeskPRO\Component\Util\LazyPropObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsController extends AbstractPublishController
{
    /**
     * @Route("/news.{_format}", name="portal_news", defaults={"_format":"html"},
     *     requirements={"_format":"html|rss|ics"})
     * @Route("/news", name="user_news_home")
     * @Security("is_granted('USE_NEWS')")
     * @PageHttpCache()
     *
     * @param Request $request
     * @param $_format
     *
     * @return Response
     */
    public function indexAction(Request $request, $_format, NewsCategory $category = null)
    {
        $page   = $request->query->getInt('page', 1);
        $person = $this->getCurrentPerson();

        $pager = $this->getNewsPager($request, $page, $person, $category);

        // RSS

        if ('rss' === $_format) {
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

        // iCalendar

        if ('ics' === $_format) {
            return $this->render('PortalBundle:News:feed.ics.twig', [
                'page_title' => $this->createPageTitle()->news(),
                'pager'      => $pager,
                'category'   => null,
            ], new Response(null, Response::HTTP_OK, ['Content-Type' => 'text/calendar']));
        }
        if ($category) {
            $icsLink = preg_replace('/https?/', 'webcal', $this->generateUrl(
                'portal_news_browse',
                ['slug' => $category->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
            $downloadCalendar = $this->generateUrl('portal_news_download_category_ics', ['slug' => $category->getSlug()]);
            $breadcrumbs      = $this->getBreadcrumbGenerator()->buildNewsCategory($category);
            $pageTitle        = $this->createPageTitle()->news($category);
        } else {
            $icsLink = preg_replace('/https?/', 'webcal', $this->generateUrl(
                'portal_news',
                ['_format' => 'ics'],
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
            $downloadCalendar = $this->generateUrl('portal_news_download_ics');
            $breadcrumbs      = $this->getBreadcrumbGenerator()->buildNews();
            $pageTitle        = $this->createPageTitle()->news();
        }

        // RENDER THEME

        $newsData = new LazyPropObject([
            'categories' => function () {
                return $this->getNewsDataService()->getCategoryList($this->getUser());
            },

            'ymCounts' => function () use ($category) {
                return $this->getNewsDataService()->getMonthsWithPosts($category, $this->getUser());
            },
        ]);

        $filterDate = $request->query->get('date');
        $filterYear = $filterDate
            ? preg_replace('/\-[0-9]{2}$/', '', $filterDate)
            : (new \DateTime())->format('Y')
        ;

        return $this->renderThemeView(
            'Theme:News:index.html.twig',
            [
                'page'                   => $page,
                'count'                  => $this->getBrandSetting('portal.per_page_content'),
                'main_class'             => 'dp-po-news',
                'viewCategory'           => $category,
                'newsData'               => $newsData,
                'pager'                  => $pager,
                'page_title'             => $pageTitle,
                'breadcrumbs'            => $breadcrumbs,
                'rss_link'               => $rssLink,
                'ics_link'               => $icsLink,
                'download_calendar'      => $downloadCalendar,
                'is_subscribed'          => $this->isSubscribedRootCategory(),
                'is_subscribed_category' => $this->isSubscribedCategory($category),
                'filter_date'            => $filterDate,
                'filter_year'            => $filterYear,
            ]
        );
    }

    /**
     * @Route("/news/download_calendar", name="portal_news_download_ics")
     * @Security("is_granted('USE_NEWS')")
     * @PageHttpCache()
     *
     * @param NewsCategory|null $category
     *
     * @return Response
     */
    public function downloadCalendarAction(NewsCategory $category = null)
    {
        if ($category) {
            $downloadLink = $this->generateUrl(
                'portal_news_browse',
                ['_format' => 'ics', 'slug' => $category->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNewsCategory($category);
            $pageTitle   = $this->createPageTitle()->news($category);
        } else {
            $downloadLink = $this->generateUrl(
                'portal_news',
                ['_format' => 'ics'],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildNews();
            $pageTitle   = $this->createPageTitle()->news();
        }
        $icsLink = preg_replace('/https?/', 'webcal', $downloadLink);

        return $this->renderThemeView(
            'Theme:News:calendar_download.html.twig',
            [
                'download_link'     => $downloadLink,
                'page_title'        => $pageTitle,
                'breadcrumbs'       => $breadcrumbs,
                'ics_link'          => $icsLink,
            ]
        );
    }

    /**
     * @Route("/news/{slug}/download_calendar", name="portal_news_download_category_ics")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_NEWS') and is_granted('VIEW_NEWS_CATEGORY', category)")
     * @PageHttpCache()
     *
     * @param NewsCategory $category
     *
     * @return Response
     */
    public function downloadCategoryCalendarAction(NewsCategory $category = null)
    {
        return $this->downloadCalendarAction($category);
    }

    /**
     * @Route("/news/{slug}.{_format}", name="portal_news_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss|ics"})
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
        if ($this->isHelpCenterTheme()) {
            return $this->indexAction($request, $_format, $category);
        }

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

        // PAGER

        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getNewsDataService()->getNewsPager($category, $page, $count, $person);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:News:browse.html.twig',
            [
                'category'      => $category,
                'is_subscribed' => $this->isSubscribedCategory($category),
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

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // REGISTERED PAGE VIEW LOG
        if ($person = $this->getUser()) {
            $this->container->get('content.page_view')->pageView($person, PageViewLog::TYPE_NEWS, $post->getId());
        }

        // OTHER ARTICLE DATA
        $postData = new LazyPropObject([
            'comments' => function () use ($post) {
                return $this->getNewsDataService()->getPostComments($post, $this->getUser());
            },
            'related_content' => function () use ($post) {
                $relatedFinder = new RelatedContentFinder($this->getCurrentPerson(), $post);

                return $relatedFinder->getRelatedEntities(true);
            },
        ]);

        // RENDER THEME

        $viewVars = [
            'post'                   => $post,
            'content'                => $post,
            'postData'               => $postData,
            'is_subscribed'          => $this->isSubscribedNewsPost($post),
            'is_subscribed_root'     => $this->isSubscribedRootCategory(),
            'is_subscribed_category' => $this->isSubscribedCategory($post->getCategory()),
            'rating'                 => $rating,
            'category'               => $post->getCategory(),
            'content_id'             => $post->getId(),
            'content_type'           => News::CONTENT_TYPE,
            'new_comment_form'       => $newCommentForm ? $newCommentForm->createView() : null,
            'page_title'             => $this->createPageTitle()->news($post),
            'breadcrumbs'            => $breadcrumbs,
            'show_rating_counts'     => $showRatingCounts,
            'rating_counts'          => $ratingCounts,
            'lockout'                => $check->isLockoutRecommended(),
            'lockout_time'           => $check->getLockoutTime(true),
            'main_class'             => 'dp-po-news-post',
            'helpcenter'             => $this->get('helpcenter_data_helper'),
        ];

        if (!$this->getUser() || $this->getUser()->getId()) {
            $viewVars = array_merge($viewVars, $this->getAuthComponents($request));
        }

        return $this->renderThemeView(
            'Theme:News:view.html.twig',
            $viewVars
        );
    }

    /**
     * Need to force a redirect here to support old permalinks!
     *
     * @Route("/news/view/{slug}", name="portal_news_view_LEGACY")
     *
     * @param string $slug
     *
     * @throws \Exception
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

        $this->addFlash('success', $this->phrase(['portal.flashes.rating_thanks', 'helpcenter.flashes.content_rating_thanks']));

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
            $this->addFlash('success', $this->phrase(['portal.flashes.news_unsubscribe', 'helpcenter.flashes.news_unsubscribe']));
        } else {
            $subscriptionsHelper->subscribeToContent($post, $person);
            $this->addFlash('success', $this->phrase(['portal.flashes.news_subscribe', 'helpcenter.flashes.news_subscribe']));
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
            $this->addFlash('success', $this->phrase(['portal.flashes.news_cat_unsubscribe', 'helpcenter.flashes.news_cat_unsubscribe'], ['category' => $category->getTitle()]));
        } else {
            $subscriptionsHelper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase(['portal.flashes.news_cat_subscribe', 'helpcenter.flashes.news_cat_subscribe'], ['category' => $category->getTitle()]));
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
            $this->addFlash('success', $this->phrase(['portal.flashes.news_cat_unsubscribe', 'helpcenter.flashes.news_root_unsubscribe']));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('news', $person);
            $this->addFlash('success', $this->phrase(['portal.flashes.news_cat_subscribe', 'helpcenter.flashes.news_root_subscribe']));
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

        $this->addFlash('success', $this->phrase(['portal.flashes.news_unsubscribe_everything', 'helpcenter.flashes.news_unsubscribe_everything']));

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

        // OTHER ARTICLE DATA
        $postData = new LazyPropObject([
            'comments' => function () use ($post) {
                return $this->getNewsDataService()->getPostComments($post, $this->getUser());
            },
            'related_content' => function () use ($post) {
                $relatedFinder = new RelatedContentFinder($this->getCurrentPerson(), $post);

                return $relatedFinder->getRelatedEntities(true);
            },
        ]);

        $contentHtml = $this->renderThemeView(
            'Theme:News:pdf.html.twig',
            [
                'post'               => $post,
                'postData'           => $postData,
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

    /**
     * @param Request                                                                    $request
     * @param int                                                                        $page
     * @param \Application\DeskPRO\Entity\Person|\Application\DeskPRO\People\PersonGuest $person
     * @param NewsCategory                                                               $cat
     *
     * @return \Pagerfanta\Pagerfanta
     */
    private function getNewsPager(Request $request, $page, $person, $cat = null)
    {
        return $this->getNewsDataService()->getNewsPager(
            $cat,
            $page,
            $request->query->getInt('per_page', $this->getBrandSetting('portal.per_page_rss')),
            $person,
            ['date' => $request->query->get('date')]
        );
    }

    /**
     * @return bool
     */
    private function isSubscribedRootCategory()
    {
        if ($this->getUser() && $this->getBrandSetting('user.news_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            return $this->getSubscriptionsHelper()->isSubscribedRootCategory('news', $this->getUser());
        }

        return false;
    }

    /**
     * @param NewsCategory $category
     *
     * @return bool
     */
    private function isSubscribedCategory(NewsCategory $category = null)
    {
        if (
            $category
            && $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS_CATEGORY, $category)
        ) {
            return $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        return false;
    }

    /**
     * @param News $post
     *
     * @return bool
     */
    private function isSubscribedNewsPost(News $post)
    {
        if (
            $this->getBrandSetting('user.news_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_NEWS, $post)
        ) {
            return $this->getSubscriptionsHelper()->isSubscribedContent($post, $this->getUser());
        }

        return false;
    }
}
