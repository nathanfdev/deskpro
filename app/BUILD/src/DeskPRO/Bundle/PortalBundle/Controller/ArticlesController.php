<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Notifications\NewCommentNotification;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\ShareContentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ShareContentVoter;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
use DeskPRO\Component\Pdf\PdfRendererInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/kb.{_format}", name="portal_kb", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/kb", name="user_articles_home")
     * @Security("is_granted('USE_ARTICLES')")
     * @PageHttpCache
     *
     * @param Request $request
     * @param $_format
     *
     * @return Response
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        // RSS

        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                null,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person,
                true
            );

            return $this->render(
                'PortalBundle:Articles:feed.rss.twig',
                [
                    'pager'      => $pager,
                    'category'   => null,
                    'page_title' => $this->get('portal_view.page_title_generator')->kb(),
                ]
            );
        }
        $rssLink = $this->generateUrl(
            'portal_kb',
            ['_format' => 'rss']
        );

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildKb();

        // SUBSCRIPTION

        $isSubscribed = false;
        if ($this->getUser() && $this->getBrandSetting('user.kb_subscriptions', false)) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedRootCategory('kb', $this->getUser());
        }

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Articles:index.html.twig',
            [
                'page'          => $page,
                'count'         => $this->getBrandSetting('portal.per_page_content'),
                'breadcrumbs'   => $breadcrumbs,
                'page_title'    => $this->get('portal_view.page_title_generator')->kb(),
                'rss_link'      => $rssLink,
                'is_subscribed' => $isSubscribed,
            ]
        );
    }

    /**
     * @Route("/kb/{slug}.{_format}", name="portal_kb_browse", defaults={"_format":"html"})
     * @Route("/kb/{slug}", name="user_articles")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE_CATEGORY', category)")
     * @PageHttpCache
     *
     * @param Request         $request
     * @param ArticleCategory $category
     * @param $_format
     *
     * @return Response
     */
    public function browseAction(Request $request, ArticleCategory $category, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        // RSS

        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                $category,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person,
                false
            );

            return $this->render(
                'PortalBundle:Articles:feed.rss.twig',
                [
                    'pager'      => $pager,
                    'category'   => $category,
                    'page_title' => $this->get('portal_view.page_title_generator')->kb($category),
                ]
            );
        }
        $rssLink = $this->generateUrl('portal_kb_browse', ['slug' => $category->getSlug(), '_format' => 'rss']);

        // BREADCRUMBS

        if ($category) {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildKbCategory($category);
        } else {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildKb();
        }

        // SUBSCRIPTION

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORY, $category)
        ) {
            // waiting info regarding article category subscriptions
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        // PAGER

        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getArticlesDataService()->getArticlesPager($category, $page, $count, $person, false);

        // RENDER THEME

        return $this->renderThemeView(
            'Theme:Articles:browse.html.twig',
            [
                'category'      => $category,
                'breadcrumbs'   => $breadcrumbs,
                'page_title'    => $this->get('portal_view.page_title_generator')->kb($category),
                'is_subscribed' => $isSubscribed,
                'pager'         => $pager,
                'count'         => $count,
                'page'          => $page,
                'rss_link'      => $rssLink,
            ]
        );
    }

    /**
     * @Route("/kb/articles/{slug}", name="portal_kb_view")
     * @Route("/kb/articles/{slug}", name="user_articles_article")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE', article)")
     * @PageHttpCache(content="article")
     *
     * @param Request $request
     * @param Article $article
     * @param $visitor_id
     *
     * @return Response
     */
    public function viewAction(Request $request, Article $article, $visitor_id)
    {
        // COMMENT FORM

        $newCommentForm = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_ARTICLE, $article)) {
            $formHandler = $this->get('form_handler.comment');
            $comment     = new ArticleComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $newCommentForm = $formHandler->createForm($comment, $request);
            $formResult     = $formHandler->handle($newCommentForm, $request, $article, $comment);
            if ($formResult) {
                $notify = new NewCommentNotification($comment);
                $notify->send();
            }
            if ($formResult instanceof Response) {
                return $formResult;
            }
        }

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildKbArticle($article);

        // RATING

        $rating = $this->findContentRating($article, $visitor_id);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($article);

        // SUBSCRIPTION

        $isSubscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE, $article)
        ) {
            // waiting on info on the kb subs
            $isSubscribed = $this->getSubscriptionsHelper()->isSubscribedContent($article, $this->getUser());
        }

        $canShare = $this->isGranted(ShareContentVoter::SHARE_ARTICLES);

        $check = new SubmitCommentAbuseCheck($this->getUser(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        // REGISTERED PAGE VIEW LOG
        if ($person = $this->getUser()) {
            $this->container->get('content.page_view')->pageView($person, PageViewLog::TYPE_ARTICLE, $article->getId());
        }

        // RENDER THEME

        $customData = [];
        foreach ($this->getEm()->getRepository(CustomDefArticle::class)->getEnabledUserFields() as $def) {
            if ($def->getParent()) {
                continue;
            }
            if ($data = $article->getCustomDataForField($def)) {
                if (is_array($data)) {
                    $val = [];
                    foreach ($data as $datum) {
                        $val[] = $this->get('data.custom_field_util')->getValueForCustomFormField($def, $datum);
                    }
                } else {
                    $val = $this->get('data.custom_field_util')->getValueForCustomFormField($def, $data);
                }
            } else {
                $val = '';
            }
            $customData[] = [
                'type'  => $def->type,
                'label' => $def->getTitle(),
                'value' => $val,
            ];
        }

        return $this->renderThemeView(
            'Theme:Articles:view.html.twig',
            [
                'article'            => $article,
                'custom_data'        => $customData,
                'rating'             => $rating,
                'is_subscribed'      => $isSubscribed,
                'category'           => $article->getPrimaryCategory(),
                'breadcrumbs'        => $breadcrumbs,
                'content_id'         => $article->getId(),
                'content_type'       => Article::CONTENT_TYPE,
                'page_title'         => $this->get('portal_view.page_title_generator')->kb($article),
                'new_comment_form'   => $newCommentForm ? $newCommentForm->createView() : null,
                'show_rating_counts' => $showRatingCounts,
                'rating_counts'      => $ratingCounts,
                'can_share'          => $canShare,
                'lockout'            => $check->isLockoutRecommended(),
                'lockout_time'       => $check->getLockoutTime(true),
            ]
        );
    }

    /**
     * @Route("/kb/articles/{slug}/vote-up", name="portal_kb_article_vote_up", defaults={"up_or_down":"up"})
     * @Route("/kb/articles/{slug}/vote-down", name="portal_kb_article_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('RATE_ARTICLE', article)")
     * @AutoPostOnGetRequest()
     *
     * @param Article $article
     * @param         $visitor_id
     * @param         $up_or_down
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleRateAction(Article $article, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($article, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($article, $visitor_id, $person);
        }

        $this->addFlash('success', $this->phrase('portal.flashes.rating_thanks'));

        return $this->redirectToRoute('portal_kb_view', ['slug' => $article->getSlug()]);
    }

    /**
     * @Route("/kb/articles/{slug}/toggle-subscription", name="portal_kb_article_toggle_subscription")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE', article)")
     * @AutoPostOnGetRequest()
     *
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleSubscriptionAction(Article $article)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedContent($article, $person)) {
            $subscriptionsHelper->unsubscribeFromContent($article, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToContent($article, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_subscribe'));
        }

        return $this->redirectToRoute('portal_kb_view', ['slug' => $article->getSlug()]);
    }

    /**
     * @Route("/kb/category/toggle-subscription/{slug}", name="portal_kb_article_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     *
     * @param ArticleCategory $category
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function articleCategorySubscriptionAction(ArticleCategory $category)
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedCategory($category, $person)) {
            $subscriptionsHelper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_kb_browse', ['slug' => $category->getSlug()]);
    }

    /**
     * @Route("/kb/root/toggle-subscription", name="portal_kb_article_root_category_toggle_subscription")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_ARTICLES')")
     * @AutoPostOnGetRequest()
     */
    public function articleRootCategorySubscriptionAction()
    {
        $person              = $this->getUser();
        $subscriptionsHelper = $this->getSubscriptionsHelper();

        if ($subscriptionsHelper->isSubscribedRootCategory('kb', $person)) {
            $subscriptionsHelper->unsubscribeFromRootCategory('kb', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_unsubscribe'));
        } else {
            $subscriptionsHelper->subscribeToRootCategory('kb', $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_kb');
    }

    /**
     * @Route("/kb/articles/subscriptions/unsubscribe", name="portal_kb_unsubscribe_all")
     * NOTE: we don't check if they have access to this content, because we might
     *       let someone UN-subscribe from all even if they don't have access to some
     *       of the categories anymore
     * @Security("is_granted('ROLE_USER') and is_granted('USE_ARTICLES')")
     * @AutoPostOnGetRequest()
     */
    public function articleUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('kb', $this->getUser());

        $this->addFlash('success', $this->phrase('portal.flashes.article_unsubscribe_everything'));

        return $this->redirectToRoute('portal_home');
    }

    /**
     * @Route("/kb/articles/pdf/{slug}", name="portal_articles_pdf")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE', article)")
     *
     * @param Article $article
     * @param int     $visitor_id
     *
     * @return Response
     */
    public function pdfAction(Article $article, $visitor_id)
    {
        /** @var PdfRendererInterface $pdfRenderer */
        $pdfRenderer = $this->get('pdf_renderer');

        // BREADCRUMBS

        $breadcrumbs = $this->getBreadcrumbGenerator()->buildKbArticle($article);

        // RATING

        $rating = $this->findContentRating($article, $visitor_id);

        // NUM RATINGS

        list($showRatingCounts, $ratingCounts) = $this->determineRatingCounts($article);

        $contentHtml = $this->renderThemeView(
            'Theme:Articles:pdf.html.twig',
            [
                'article'            => $article,
                'rating'             => $rating,
                'category'           => $article->getPrimaryCategory(),
                'breadcrumbs'        => $breadcrumbs,
                'content_id'         => $article->getId(),
                'content_type'       => Article::CONTENT_TYPE,
                'page_title'         => $this->get('portal_view.page_title_generator')->kb($article),
                'show_rating_counts' => $showRatingCounts,
            ]
        );

        return $pdfRenderer->generateFile($contentHtml->getContent(), $article->getTranslatedTitle().'.pdf');
    }

    /**
     * @Route("/kb/articles/share/{slug}", name="portal_articles_share")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SHARE_ARTICLES') and is_granted('VIEW_ARTICLE', article)")
     *
     * @param Request $request
     * @param Article $article
     *
     * @return Response
     */
    public function shareAction(Request $request, Article $article)
    {
        $form = $this->createForm('share_article');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->runAntiAbuseCheck($request, $article);
            $emails = [];
            $email  = $form->getViewData()['email'];
            /** @var Person $person */
            if ($person = $this->getEm()->getRepository(Person::class)->findOneByEmail($email)) {
                $emails[] = $person;
            } else {
                $emails[] = ['address' => $email, 'name' => $form->getViewData()['name']];
            }

            if ($form->getViewData()['send_myself']) {
                $emails[] = $this->getUser();
            }

            $this->getEmailSender()->sendShareArticle($article, $this->getUser(), $emails, $form->getViewData());

            $this->addFlash('success', $this->phrase('portal.flashes.email_sent'));

            return $this->redirectToRoute('portal_kb_view', ['slug' => $article->getSlug()]);
        }

        $check = new ShareContentAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $check->markAsCheckOnly();
        $this->get('anti_abuse')->check($check);

        return $this->renderThemeView(
            'Theme:Articles:share.html.twig',
            [
                'article'      => $article,
                'form'         => $form->createView(),
                'form_errors'  => $form->isSubmitted() ? $form->getErrors() : [],
                'lockout'      => $check->isLockoutRecommended(),
                'lockout_time' => $check->getLockoutTime(true),
            ]
        );
    }

    private function runAntiAbuseCheck(Request $request, Article $article)
    {
        $check = new ShareContentAbuseCheck($this->getCurrentPerson(), $request->getClientIp());
        $check
            ->setResponse(
                $this->redirectToRoute(
                    'portal_articles_share',
                    [
                        'slug'    => $article->getSlug(),
                        'lockout' => 'share',
                    ]
                )
            );
        $this->get('anti_abuse')->check($check);
    }
}
