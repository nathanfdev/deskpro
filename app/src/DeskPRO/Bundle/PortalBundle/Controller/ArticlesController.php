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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentCommentVoter;
use DeskPRO\Bundle\AppBundle\Security\Voter\Portal\ContentSubscriptionsVoter;
use DeskPRO\Bundle\PortalBundle\HttpCache\Configuration\PageHttpCache;
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
     */
    public function indexAction(Request $request, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                null,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array(
                'pager'      => $pager,
                'category'   => null,
                'page_title' => $this->get('portal_view.page_title_generator')->kb(),
            ));
        }
        $rss_link = $this->generateUrl(
            'portal_kb',
            array('_format' => 'rss')
        );

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildKb();

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:index.html.twig',
            array(
                'page'        => $page,
                'count'       => $this->getBrandSetting('portal.per_page_content'),
                'breadcrumbs' => $breadcrumbs,
                'page_title'  => $this->get('portal_view.page_title_generator')->kb(),
                'rss_link'    => $rss_link,
            )
        );
    }

    /**
     * @Route("/kb/{slug}.{_format}", name="portal_kb_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Route("/kb/{slug}", name="user_articles")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE_CATEGORY', category)")
     * @PageHttpCache
     */
    public function browseAction(Request $request, ArticleCategory $category, $_format)
    {
        $page   = $request->get('page', 1);
        $person = $this->getCurrentPerson();

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                $category,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss')),
                $person
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array(
                'pager'      => $pager,
                'category'   => $category,
                'page_title' => $this->get('portal_view.page_title_generator')->kb($category),
            ));
        }
        $rss_link = $this->generateUrl('portal_kb_browse', array('slug' => $category->getSlug(), '_format' => 'rss'));

        //
        // BREADCRUMBS
        //
        if ($category) {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildKbCategory($category);
        } else {
            $breadcrumbs = $this->getBreadcrumbGenerator()->buildKb();
        }

        //
        // SUBSCRIPTION
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORY, $category)
        ) {
            // waiting info regarding article category subscriptions
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }

        //
        // PAGER
        //
        $count = $this->getBrandSetting('portal.per_page_content');
        $pager = $this->getArticlesDataService()->getArticlesPager($category, $page, $count, $person);

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:browse.html.twig',
            array(
                'category'      => $category,
                'breadcrumbs'   => $breadcrumbs,
                'page_title'    => $this->get('portal_view.page_title_generator')->kb($category),
                'is_subscribed' => $is_subscribed,
                'pager'         => $pager,
                'count'         => $count,
                'page'          => $page,
                'rss_link'      => $rss_link,
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}", name="portal_kb_view")
     * @Route("/kb/articles/{slug}", name="user_articles_article")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE', article)")
     * @PageHttpCache(content="article")
     */
    public function viewAction(Request $request, Article $article, $visitor_id)
    {
        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_ARTICLE, $article)) {
            $form_handler = $this->get('form_handler.comment');
            $comment      = new ArticleComment();
            $comment->setVisitorId($visitor_id);
            $comment->setIpAddress($request->getClientIp());
            $new_comment_form = $form_handler->createForm($comment, $request);
            $form_result      = $form_handler->handle($new_comment_form, $request, $article, $comment);
            if ($form_result instanceof Response) {
                return $form_result;
            }
        }

        //
        // BREADCRUMBS
        //
        $breadcrumbs = $this->getBreadcrumbGenerator()->buildKbArticle($article);

        //
        // RATING
        //
        $rating = $this->findContentRating($article, $visitor_id);

        //
        // NUM RATINGS
        //
        list($show_rating_counts, $rating_counts) = $this->determineRatingCounts($article);

        //
        // SUBSCRIPTION
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE, $article)
        ) {
            // waiting on info on the kb subs
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($article, $this->getUser());
        }

        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:view.html.twig',
            array(
                'article'            => $article,
                'rating'             => $rating,
                'is_subscribed'      => $is_subscribed,
                'category'           => $article->getPrimaryCategory(),
                'breadcrumbs'        => $breadcrumbs,
                'content_id'         => $article->getId(),
                'content_type'       => Article::CONTENT_TYPE,
                'page_title'         => $this->get('portal_view.page_title_generator')->kb($article),
                'new_comment_form'   => $new_comment_form ? $new_comment_form->createView() : null,
                'show_rating_counts' => $show_rating_counts,
                'rating_counts'      => $rating_counts,
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}/vote-up", name="portal_kb_article_vote_up", defaults={"up_or_down":"up"})
     * @Route("/kb/articles/{slug}/vote-down", name="portal_kb_article_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('RATE_ARTICLE', article)")
     * @AutoPostOnGetRequest()
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

        return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
    }

    /**
     * @Route("/kb/articles/{slug}/toggle-subscription", name="portal_kb_article_toggle_subscription")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE', article)")
     * @AutoPostOnGetRequest()
     */
    public function articleSubscriptionAction(Article $article)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($article, $person)) {
            $subscriptions_helper->unsubscribeFromContent($article, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToContent($article, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_subscribe'));
        }

        return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
    }

    /**
     * @Route("/kb/category/toggle-subscription/{slug}", name="portal_kb_article_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE_CATEGORY', category)")
     * @AutoPostOnGetRequest()
     */
    public function articleCategorySubscriptionAction(ArticleCategory $category)
    {
        $person               = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedCategory($category, $person)) {
            $subscriptions_helper->unsubscribeFromCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_unsubscribe'));
        } else {
            $subscriptions_helper->subscribeToCategory($category, $person);
            $this->addFlash('success', $this->phrase('portal.flashes.article_cat_subscribe'));
        }

        return $this->redirectToRoute('portal_kb_browse', array('slug' => $category->getSlug()));
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
}
