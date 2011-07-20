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
use Application\DeskPRO\Entity\ArticleValidatingEdit;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use FineDiff;

/**
 * Handles ticket searches
 */
class KbController extends AbstractController
{
	############################################################################
	# Edit article
	############################################################################

	public function viewArticleAction($article_id)
	{
		$article_categories = array();
		$article_products   = array();
		$pending_article    = null;
		$validating_edit    = null;

		if ($article_id) {
			$article = App::findEntity('DeskPRO:Article', $article_id);
			if (!$article) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown article $article_id");
			}

			$article_categories = App::getDb()->fetchAllCol("
				SELECT category_id
				FROM article_to_categories
				WHERE article_id = ?
			", array($article['id']));

			$article_products = App::getDb()->fetchAllCol("
				SELECT product_id
				FROM article_to_product
				WHERE article_id = ?
			", array($article['id']));

			if ($this->in->getBool('do_validate') AND $article['status_code'] == 'hidden.validating') {
				$article['status_code'] = Article::STATUS_PUBLISHED;
				App::getOrm()->persist($article);
				App::getOrm()->flush();
			}

			// Check if this user has an edit for this article
			$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article, $this->person);

		} else {
			$article = new Article();

			if ($this->in->getUint('pending_article_id')) {
				$pending_article = App::findEntity('DeskPRO:ArticlePendingCreate', $this->in->getUint('pending_article_id'));
			}

			if ($this->in->getUint('in_category')) {
				$article_categories[] = $this->in->getUint('in_category');
			}
		}

		/** @var $agent_cat_helper \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy */
		$agent_cat_helper = App::getEntityRepository('DeskPRO:ArticleCategory')->getAgentCategoryHelper();
		$agent_category_names = $agent_cat_helper->getFullCategoryNames();

		/** @var $user_cat_helper \Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy */
		$user_cat_helper = App::getEntityRepository('DeskPRO:ArticleCategory')->getUserCategoryHelper();
		$user_category_names = $user_cat_helper->getFullCategoryNames();

		$product_name   = App::getEntityRepository('DeskPRO:Product')->getFullCategoryNames();

		$tpl = 'AgentBundle:Kb:edit.html.twig';
		if ($this->in->getBool('view')) {
			$tpl = 'AgentBundle:Kb:view.html.twig';
		}
		$tpl = 'AgentBundle:Kb:view.html.twig';
		if (!$article['id']) {
			$tpl = 'AgentBundle:Kb:new.html.twig';
		}

		return $this->render($tpl, array(
			'article'              => $article,
			'user_category_names'  => $user_category_names,
			'agent_category_names' => $agent_category_names,
			'product_names'        => $product_name,
			'article_categories'   => $article_categories,
			'article_products'     => $article_products,
			'pending_article'      => $pending_article,
			'validating_edit'      => $validating_edit,
		));
	}

	public function newArticleSaveAction()
	{
		$article = new Article();
		$article['title'] = $this->in->getString('article.title');
		$article['content'] = $this->in->getString('article.content');
		$article['status_code'] = $this->in->getString('article.status_code');
		$article->person = $this->person;

		if ($this->in->getString('article.date_published')) {
			$article['date_published'] = new \DateTime($this->in->getString('article.date_published'));
		}
		if ($this->in->getString('article.date_end')) {
			$article['date_end'] = new \DateTime($this->in->getString('article.date_end'));
		}

		$cat_ids = $this->in->getCleanValueArray('article.categories', 'uint', 'discard');
		$cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoriesById($cat_ids);
		foreach ($cats as $c) {
			$article->categories->add($c);
		}

		$product_ids = $this->in->getCleanValueArray('article.products', 'uint', 'discard');
		$products = App::getEntityRepository('DeskPRO:Product')->getProductsById($product_ids);
		foreach ($products as $p) {
			$article->products->add($p);
		}

		$pending_article_id = false;
		if ($this->in->getUint('pending_article_id')) {
			$pending_article = App::findEntity('DeskPRO:ArticlePendingCreate', $this->in->getUint('pending_article_id'));
			$pending_article_id = $pending_article['id'];
			App::getOrm()->remove($pending_article);
		}

		App::getOrm()->persist($article);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'load_url'   => $this->generateUrl('agent_kb_article', array('article_id' => $article['id'])),
			'pending_article_id' => $pending_article_id,
			'article_id' => $article['id']
		));
	}

	public function editArticleSaveAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		
		$require_validating = $this->in->getBool('use_validating_edit');
		$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article, $this->person);
		if ($validating_edit) {
			// If the user already has an edit, then we're just updating that
			$require_validating = true;
		}

		if ($require_validating) {

			if (!$validating_edit) {
				$validating_edit = new ArticleValidatingEdit();
				$validating_edit->person = $this->person;
				$validating_edit->article = $article;
			}

			$validating_edit['title'] = $this->in->getString('article.title');
			$validating_edit['content'] = $this->in->getString('article.content');

			App::getOrm()->persist($validating_edit);
			App::getOrm()->flush();

		} else {
			$article['title'] = $this->in->getString('article.title');
			$article['content'] = $this->in->getString('article.content');
			$article['status_code'] = $this->in->getString('article.status_code');

			if ($this->in->getString('article.date_published')) {
				$article['date_published'] = new \DateTime($this->in->getString('article.date_published'));
			}
			if ($this->in->getString('article.date_end')) {
				$article['date_end'] = new \DateTime($this->in->getString('article.date_end'));
			}

			$article->categories->clear();
			$article->products->clear();

			$cat_ids = $this->in->getCleanValueArray('article.categories', 'uint', 'discard');
			$cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoriesById($cat_ids);
			foreach ($cats as $c) {
				$article->categories->add($c);
			}

			$product_ids = $this->in->getCleanValueArray('article.products', 'uint', 'discard');
			$products = App::getEntityRepository('DeskPRO:Product')->getProductsById($product_ids);
			foreach ($products as $p) {
				$article->products->add($p);
			}

			App::getOrm()->persist($article);
			App::getOrm()->flush();
		}

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function ajaxSaveLabelsAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$article->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($article);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function getArticleEditorAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);

		$tpl = 'view-editor-markdown.html.twig';
		if ($article['markup_mode'] == Article::MARKUP_MODE_HTML) {
			$tpl = 'view-editor-html.html.twig';
		}

		return $this->render("AgentBundle:Kb:$tpl", array(
			'article' => $article
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

		$row_html = $this->renderView('AgentBundle:Kb:list-pending-row.html.twig', array('pending_article' => $pending_article));

		return $this->createJsonResponse(array(
			'row_html' => $row_html,
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

	public function previewArticleAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);

		return $this->render('AgentBundle:Kb:preview-validating-article.html.twig', array(
			'article' => $article,
		));
	}

	public function previewEditAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article);
		
		return $this->render('AgentBundle:Kb:preview-validating-edit.html.twig', array(
			'article' => $article,
			'edit' => $validating_edit,
		));
	}

	public function validateArticleJsonAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article);

		// Edit existsmeans we're validating that
		if ($validating_edit) {
			$article['title'] = $validating_edit['title'];
			$article['content'] = $validating_edit['content'];
			$type = 'edit';
		} else {
			$article['status_code'] = 'published';
			$type = 'article';
		}

		App::getOrm()->persist($article);
		App::getOrm()->flush();



		return $this->createJsonResponse(array(
			'article_id' => $article['id'],
			'next_article_id' => $this->_getNextValidateArticle(),
			'type' => $type,
		));
	}

	public function disapproveValidateArticleJsonAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article);

		if ($validating_edit) {
			App::getOrm()->remove($validating_edit);
			$type = 'edit';
		} else {
			App::getOrm()->remove($article);
			$type = 'article';
		}

		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'article_id' => $article['id'],
			'next_article_id' => $this->_getNextValidateArticle(),
			'type' => $type,
		));
	}

	public function getNextValidatingArticleJsonAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		$validating_edit = App::getEntityRepository('DeskPRO:ArticleValidatingEdit')->getEditForArticle($article);

		$next_id = App::getDb()->fetchColumn("
			SELECT id
			FROM articles
			WHERE hidden_status = 'validating' AND article_id != ?
			ORDER BY id DESC
		", array($article['id']));

		if (!$next_id) {
			App::getDb()->fetchColumn("
				SELECT article_id
				FROM article_validating_edits
				WHERE article_id != ?
				ORDER BY id DESC
			", arrya($article['id']));
		}

		return $this->createJsonResponse(array('next_article_id' => $next_id));
	}

	protected function _getNextValidateArticle()
	{
		$next_id = App::getDb()->fetchColumn("SELECT id FROM articles WHERE hidden_status = 'validating' ORDER BY id DESC");
		if (!$next_id) {
			App::getDb()->fetchColumn("SELECT article_id FROM article_validating_edits ORDER BY id DESC");
		}
		
		return $next_id;
	}

	############################################################################
	# Listings
	############################################################################

	public function draftListAction()
	{
		$all_articles = App::getEntityRepository('DeskPRO:Article')->getDraftArticles();

		$your_articles = array();
		$others_articles = array();

		foreach ($all_articles as $article) {
			if ($article->person['id'] == $this->person['id']) {
				$your_articles[] = $article;
			} else {
				$others_articles[] = $article;
			}
		}

		return $this->render('AgentBundle:Kb:list-drafts.html.twig', array(
			'your_articles' => $your_articles,
			'others_articles' => $others_articles,
		));
	}

	public function categoryListAction($category_id)
	{
		$category = App::findEntity('DeskPRO:ArticleCategory', $category_id);

		$is_agent = $category['is_agent'];

		$all_cat_ids = $category->getTreeIds();

		$articles = App::getOrm()->createQuery("
			SELECT a
			FROM DeskPRO:Article a INDEX BY a.id
			LEFT JOIN a.categories cat
			LEFT JOIN a.person p
			WHERE cat.id IN (" . implode(',', $all_cat_ids) . ")
				AND ((a.status = 'published' OR a.status = 'archived') OR (a.hidden_status = 'unpublished'))
			ORDER BY a.id DESC
		")->execute();

		return $this->render('AgentBundle:Kb:list.html.twig', array(
			'category' => $category,
			'articles' => $articles,
			'is_agent' => $is_agent
		));
	}

	############################################################################
	# Validating comments
	############################################################################

	public function validatingCommentsListAction()
	{
		$comments = App::getEntityRepository('DeskPRO:ArticleComment')->getValidatingComments();

		return $this->render('AgentBundle:Kb:validating-comments-list.html.twig', array(
			'comments' => $comments
		));
	}

	public function validateCommentJsonAction($comment_id)
	{
		$comment = App::findEntity('DeskPRO:ArticleComment', $comment_id);
		$comment['status'] = 'visible';

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
		));
	}

	public function disapproveValidateCommentJsonAction($comment_id)
	{
		$comment = App::findEntity('DeskPRO:ArticleComment', $comment_id);
		$comment['status'] = 'deleted';

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
		));
	}

	############################################################################
	# Compare revisions
	############################################################################

	public function compareRevisionsActions($rev_old_id, $rev_new_id)
	{
		$rev_old = App::findEntity('DeskPRO:ArticleRevision', $rev_old_id);
		$rev_new = App::findEntity('DeskPRO:ArticleRevision', $rev_new_id);

		$diff = new FineDiff(
			$rev_old['content'],
			$rev_new['content'],
			FineDiff::$wordGranularity
		);

		$edits = $diff->getOps();
		$rendered_diff = $diff->renderDiffToHTML();

		$rendered_diff = nl2br($rendered_diff);

		return $this->render('AgentBundle:Kb:compare-revs.html.twig', array(
			'rev_old' => $rev_old,
			'rev_new' => $rev_new,
			'rendered_diff' => $rendered_diff
		));
	}
}