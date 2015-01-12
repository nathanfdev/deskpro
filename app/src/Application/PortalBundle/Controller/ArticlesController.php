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


use Application\AuthBundle\Voter\Portal\ContentSubscriptionsVoter;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/kb.{_format}", name="portal_kb", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function indexAction(Request $request, $_format)
    {
        //
        // RSS
        //

        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                null,
                $request->get('page', 1),
                $request->get('per_page', 20)
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array('pager' => $pager, 'category' => null));
        }


        //
        // RENDER THEME
        //

        return $this->renderThemeView(
            'Theme:Articles:index.html.twig'
        );
    }

    /**
     * @Route("/kb/{slug}.{_format}", name="portal_kb_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function browseAction(Request $request, ArticleCategory $category, $_format)
    {
        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                $category,
                $request->get('page', 1),
                $request->get('per_page', 20)
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array('pager' => $pager, 'category' => $category));
        }


        //
        // SUBSCRIPTIONS
        //
        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedCategory($category, $this->getUser());
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:browse.html.twig',
            array(
                'category' => $category,
                'page' => $request->get('page', 1),
                'count' => 2,
                'show_pagination' => true,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}", name="portal_kb_view")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function viewAction(Request $request, Article $article)
    {
        //
        // RELATED, RATINGS, SUBSCRIPTIONS
        //
        $related = $this->getArticlesDataService()->getRelatedArticles($article);
        $rating = $this->getRatingsHelper()->getPersonRating($article, $this->getUser());

        $is_subscribed = false;
        if (
            $this->getBrandSetting('user.kb_subscriptions', false)
            && $this->isGranted(ContentSubscriptionsVoter::SUBSCRIBE_ARTICLE_CATEGORIES)
        ) {
            $is_subscribed = $this->getSubscriptionsHelper()->isSubscribedContent($article, $this->getUser());
        }


        //
        // RENDER THEME
        //

        return $this->renderThemeView(
            'Theme:Articles:view.html.twig',
            array(
                'article' => $article,
                'category' => $article->getPrimaryCategory(),
                'rating'  => $rating,
                'related_articles' => $related,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}/vote-up", name="portal_kb_article_vote_up", defaults={"up_or_down":"up"})
     * @Route("/kb/articles/{slug}/vote-down", name="portal_kb_article_vote_down", defaults={"up_or_down":"down"})
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('RATE_ARTICLES', article)")
     */
    public function articleRateAction(Article $article, $visitor_id, $up_or_down)
    {
        $person = $this->isGranted('ROLE_USER') ? $this->getUser() : null;

        if ('down' === $up_or_down) {
            $this->getRatingsHelper()->rateContentDown($article, $visitor_id, $person);
        } else {
            $this->getRatingsHelper()->rateContentUp($article, $visitor_id, $person);
        }

        return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
    }

    /**
     * @Route("/kb/articles/{slug}/toggle-subscription", name="portal_kb_article_toggle_subscription")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLES', article)")
     */
    public function articleSubscriptionAction(Article $article)
    {
        $person = $this->getUser();
        $subscriptions_helper = $this->getSubscriptionsHelper();

        if ($subscriptions_helper->isSubscribedContent($article, $person)) {
            $subscriptions_helper->unsubscribeFromContent($article, $person);
            $this->addFlash('success', 'Successfully unsubscribed from this article.');
        } else {
            $subscriptions_helper->subscribeToContent($article, $person);
            $this->addFlash('success', 'You have successfully subscribed to this article. You will be notified when it is updated.');
        }

        return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
    }


    /**
     * @Route("/kb/c/toggle-subscription/{slug}", name="portal_kb_article_category_toggle_subscription")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE_CATEGORIES', category)")
     */
    public function articleCategorySubscriptionAction(ArticleCategory $category)
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

        return $this->redirectToRoute('portal_kb_browse', array('slug' => $category->getSlug()));
    }

    /**
     * @return \Application\AppBundle\DataService\ArticlesDataService
     */
    protected function getArticlesDataService()
    {
        return $this->get('data.articles');
    }
}
