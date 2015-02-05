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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/kb.{_format}", name="portal_kb", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @Security("is_granted('USE_ARTICLES')")
     * @Cache(smaxage="10 minutes")
     */
    public function indexAction(Request $request, $_format)
    {
        $page = $request->get('page', 1);

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                null,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss'))
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array('pager' => $pager, 'category' => null));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:index.html.twig',
            array(
                'page' => $page,
                'count' => $this->getBrandSetting('portal.per_page_content'),
            )
        );
    }

    /**
     * @Route("/kb/{slug}.{_format}", name="portal_kb_browse", defaults={"_format":"html"}, requirements={"_format":"html|rss"})
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE_CATEGORY', category)")
     * @Cache(smaxage="10 minutes")
     */
    public function browseAction(Request $request, ArticleCategory $category, $_format)
    {
        $page = $request->get('page', 1);

        //
        // RSS
        //
        if ('rss' === $_format) {
            $pager = $this->getArticlesDataService()->getArticlesPager(
                $category,
                $page,
                $request->get('per_page', $this->getBrandSetting('portal.per_page_rss'))
            );

            return $this->render('PortalBundle:Articles:feed.rss.twig', array('pager' => $pager, 'category' => $category));
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:browse.html.twig',
            array(
                'category' => $category,
                'page' => $page,
                'count' => $this->getBrandSetting('portal.per_page_content'),
                'show_pagination' => true
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}", name="portal_kb_view")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('VIEW_ARTICLE', article)")
     * @Cache(smaxage="10 minutes")
     */
    public function viewAction(Request $request, Article $article)
    {
        //
        // COMMENT FORM
        //
        $new_comment_form = null;
        if ($this->isGranted(ContentCommentVoter::COMMENT_ARTICLES)) {
            $comment = new ArticleComment();
            $comment->setObject($article);
            $new_comment_form = $this->createForm('comment', $comment, array(
                'person' => $this->getUser()
            ));
            $new_comment_form->handleRequest($request);
            if ($new_comment_form->isValid()) {
                $article->addComment($comment);
                $this->getEm()->persist($comment);
                $this->getEm()->flush($comment, $article);

                return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
            }
        }


        //
        // RENDER THEME
        //
        return $this->renderThemeView(
            'Theme:Articles:view.html.twig',
            array(
                'article' => $article,
                'category' => $article->getPrimaryCategory(),
                'content_id' => $article->getId(),
                'content_type' => Article::CONTENT_TYPE,
                'new_comment_form' => $new_comment_form ? $new_comment_form->createView() : null
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
     * @Route("/kb/category/toggle-subscription/{slug}", name="portal_kb_article_category_toggle_subscription")
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
     * @Route("/kb/articles/subscriptions/unsubscribe", name="portal_kb_unsubscribe_all")
     * @Security("is_granted('ROLE_USER') and is_granted('USE_ARTICLES')")
     */
    public function articleUnsubscribeAllAction()
    {
        $this->getSubscriptionsHelper()->unsubscribeFromAll('kb', $this->getUser());

        $this->addFlash('success', 'Unsubscribed from all Knowledgebase subscriptions');

        return $this->redirectToRoute('portal_index');
    }
}
