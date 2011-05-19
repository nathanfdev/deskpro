<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticlePendingCreate;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Handles ticket searches
 */
class KbController extends AbstractController
{
	############################################################################
	# Edit article
	############################################################################

	public function editAction($article_id)
	{
		if ($article_id) {
			$article = App::findEntity('DeskPRO:Article', $article_id);
			if (!$article) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown article $article_id");
			}
		} else {
			$article = new Article();
		}

		$category_names = App::getEntityRepository('DeskPRO:ArticleCategory')->getFullCategoryNames();
		$product_name   = App::getEntityRepository('DeskPRO:Product')->getFullCategoryNames();

		$tpl = 'AgentBundle:Kb:edit.html.twig';
		if (!$article['id']) {
			$tpl = 'AgentBundle:Kb:new.html.twig';
		}

		return $this->render($tpl, array(
			'article'        => $article,
			'category_names' => $category_names,
			'product_names'  => $product_name
		));
	}

	public function newSaveAction()
	{
		$article = new Article();
		$article['title'] = $this->in->getString('article.title');
		$article['content'] = $this->in->getString('article.content');
		$article['status_code'] = $this->in->getString('article.status_code');

		$cat_ids = $this->in->getCleanValueArray('article.categories', 'uint', 'discard');
		$cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoriesById($cat_ids);
		foreach ($cats as $c) {
			$article->categories->add($cats);
		}

		$product_ids = $this->in->getCleanValueArray('article.products', 'uint', 'discard');
		$products = App::getEntityRepository('DeskPRO:Product')->getProductsById($product_ids);
		foreach ($products as $p) {
			$article->products->add($p);
		}

		App::getOrm()->persist($article);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'load_url'   => $this->generateUrl('agent_kb_article', array('article_id' => $article['id'])),
			'article_id' => $article['id']
		));
	}


	############################################################################
	# Pending articles
	############################################################################

	/**
	 * List the articles
	 */
	public function listPendingArticlesAction()
	{
		$pending_articles = App::getEntityRepository('DeskPRO:ArticlePendingCreate')->getPendingArticles();

		return $this->render('AgentBundle:Kb:list-pending-articles.html.twig', array(
			'pending_articles' => $pending_articles,
		));
	}

	
	/**
	 * [AJAX] Adds a new pending article
	 */
	public function newPendingArticleAction()
	{
		$pending_article = new ArticlePendingCreate();
		$pending_article->person = $this->person;

		if ($this->in->getUint('ticket_id')) {
			$ticket = App::findEntity('DeskPRO:Ticket', $this->in->getUint('ticket_id'));
			if ($ticket) {
				$pending_article->ticket = $ticket;
			}
		}

		$pending_article['comment'] = $this->in->getString('comment');

		App::getOrm()->persist($pending_article);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'pending_article_id' => $pending_article['id']
		));
	}

	/**
	 * [AJAX] remove a pending article
	 */
	public function removePendingArticleAction($pending_article_id)
	{
		$pending_article = App::findEntity('DeskPRO:ArticlePendingCreate', $pending_article_id);

		App::getOrm()->remove($pending_article);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'success' => true,
			'pending_article_id' => $pending_article_id,
		));
	}


	############################################################################
	# Validation
	############################################################################

	public function validatingListAction()
	{
		$validating_articles = App::getEntityRepository('DeskPRO:Article')->getValidatingArticle();
		$validating_edits    = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getValidatingEdit();

		return $this->render('AgentBundle:Kb:list-validating-articles.html.twig', array(
			'validating_articles' => $validating_articles,
			'validating_edits'    => $validating_edits,
		));
	}
}