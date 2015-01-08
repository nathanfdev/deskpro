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


use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ArticlesController extends AbstractController
{
    /**
     * @Route("/kb", name="portal_kb")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function indexAction(Request $request)
    {
        return $this->renderThemeView(
            'Theme:Articles:index.html.twig'
        );
    }

    /**
     * @Route("/kb/{slug}", name="portal_kb_browse")
     * @ParamConverter(name="category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES')")
     */
    public function browseAction(Request $request, ArticleCategory $category)
    {
        $is_subscribed = false;
        if ($this->isGranted('ROLE_USER') && $this->getBrandContainer()->getSetting('user.kb_subscriptions')) {
            $is_subscribed = $this->getDb()->fetchColumn("
                SELECT id
                FROM kb_subscriptions
                WHERE person_id = ? AND category_id = ?
            ", array($this->getUser()->getId(), $category->getId()));
        }

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
        $related = $this->getArticlesDataService()->getRelatedArticles($article);

        $is_subscribed = false;
        if ($this->isGranted('ROLE_USER') && $this->getBrandContainer()->getSetting('user.kb_subscriptions')) {
            $is_subscribed = $this->getDb()->fetchColumn("
                SELECT id
                FROM kb_subscriptions
                WHERE person_id = ? AND article_id = ?
            ", array($this->getUser()->getId(), $article->getId()));
        }

        return $this->renderThemeView(
            'Theme:Articles:view.html.twig',
            array(
                'article' => $article,
                'related_articles' => $related,
                'is_subscribed' => $is_subscribed
            )
        );
    }

    /**
     * @Route("/kb/articles/{slug}/toggle-subscription", name="portal_kb_article_toggle_subscription")
     * @ParamConverter(name="article", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLES')")
     */
    public function articleSubscriptionAction(Article $article)
    {
        $exist = $this->getDb()->fetchColumn("
            SELECT id
            FROM kb_subscriptions
            WHERE person_id = ? AND article_id = ?
        ", array($this->getUser()->getId(), $article->getId()));

        if ($exist) {
            $this->getDb()->delete('kb_subscriptions', array(
                'person_id' => $this->getUser()->getId(),
                'article_id' => $article->getId()
            ));
            $this->addFlash('success', 'Successfully unsubscribed from this article.');
        } else {
            $this->getDb()->insert('kb_subscriptions', array(
                'person_id' => $this->getUser()->getId(),
                'article_id' => $article->getId()
            ));
            $this->addFlash('success', 'You have successfully subscribed to this article. You will be notified when it is updated.');
        }


        return $this->redirectToRoute('portal_kb_view', array('slug' => $article->getSlug()));
    }


    /**
     * @Route("/kb/c/toggle-subscription/{slug}", name="portal_kb_article_category_toggle_subscription")
     * @ParamConverter(name="article_category", converter="deskpro_slug")
     * @Security("is_granted('USE_ARTICLES') and is_granted('SUBSCRIBE_ARTICLE_CATEGORIES')")
     */
    public function articleCategorySubscriptionAction(ArticleCategory $article_category)
    {
        $exist = $this->getDb()->fetchColumn("
            SELECT id
            FROM kb_subscriptions
            WHERE person_id = ? AND category_id = ?
        ", array($this->getUser()->getId(), $article_category->getId()));

        if ($exist) {
            $this->getDb()->delete('kb_subscriptions', array(
                'person_id' => $this->getUser()->getId(),
                'category_id' => $article_category->getId()
            ));
            $this->addFlash('success', 'Successfully unsubscribed from this category.');
        } else {
            $this->getDb()->insert('kb_subscriptions', array(
                'person_id' => $this->getUser()->getId(),
                'category_id' => $article_category->getId()
            ));
            $this->addFlash('success', 'You have successfully subscribed to this category. You will be notified when it is updated.');
        }


        return $this->redirectToRoute('portal_kb_browse', array('slug' => $article_category->getSlug()));
    }

    /**
     * @return \Application\AppBundle\DataService\ArticlesDataService
     */
    protected function getArticlesDataService()
    {
        return $this->get('data.articles');
    }
}
