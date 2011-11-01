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
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ArticleValidatingEdit;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Searcher\ArticleSearch;
use Application\DeskPRO\UI\RuleBuilder;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Publish\RelatedContentUpdate;

use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;

use Application\AgentBundle\Controller\Helper\ArticleResults;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
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
		$article = App::findEntity('DeskPRO:Article', $article_id);
		if (!$article) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown article $article_id");
		}

		if ($this->in->getBool('do_validate') AND $article['status_code'] == 'hidden.validating') {
			$article['status_code'] = Article::STATUS_PUBLISHED;
			App::getOrm()->persist($article);
			App::getOrm()->flush();
		}

		$tpl = 'AgentBundle:Kb:view.html.twig';

		$article_comments = App::getEntityRepository('DeskPRO:ArticleComment')->getComments($article);

		$article_revisions = $article->getRevisions();

		$related_finder = new RelatedContentFinder($this->person, $article);
		$related_content = $related_finder->getRelatedEntities();

		$glossary = new \Application\DeskPRO\Publish\GlossaryHandler($this->em);
		$content = $article->content;
		$content = $glossary->processText($content);

		$state = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId('agent.ui.state.editarticle', $this->person->id);

		$sticky_search_words = $this->em->getRepository('DeskPRO:SearchStickyResult')->getWordsForObject($article);

		$rated_searches = App::getEntityRepository('DeskPRO:SearchLog')->getRatedSearchesFor('article', $article['id'], 'counted');

		return $this->render($tpl, array(
			'article'              => $article,
			'sticky_search_words'  => $sticky_search_words,
			'rated_searches'       => $rated_searches,
			'content'              => $content,
			'article_comments'     => $article_comments,
			'article_revisions'    => $article_revisions,
			'related_content'      => $related_content,
			'state'                => $state,
		));
	}

	public function viewRevisionsAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		if (!$article) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown article $article_id");
		}

		$article_revisions = $article->getRevisions();

		return $this->render('AgentBundle:Kb:view-revisions-tab.html.twig', array(
			'article'              => $article,
			'article_revisions'    => $article_revisions,
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

	public function ajaxSaveAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);
		$rev = null;

		$action = $this->in->getString('action');

		$data = array('success' => 1);

		$this->em->beginTransaction();

		switch ($action) {
			case 'status':
				$article['status_code'] = $this->in->getString('status');
				break;

			case 'title':
				$article['title'] = $this->in->getString('title');

				$rev = ContentRevisionUtil::findOrCreate($article, 'title', $this->person);
				$rev['title'] = $article['title'];

				break;

			case 'delete':
				$article->status_code = 'hidden.deleted';
				break;

			case 'categories':
				$cat_ids = $this->in->getCleanValueArray('category_ids', 'uint', 'discard');
				$article->setCategories($cat_ids);
				$data['category_ids'] = $article->categories->getKeys();
				break;

			case 'products':
				$cat_ids = $this->in->getCleanValueArray('product_ids', 'uint', 'discard');
				$article->setProducts($cat_ids);
				$data['product_ids'] = $article->products->getKeys();
				break;

			case 'remove-auto-unpub':
				$article->date_end = null;
				$article->end_action = null;
				break;

			case 'auto-unpub':
				$date = date_create('@' . $this->in->getUint('end_timestamp'));
				$action = $this->in->getString('end_action');

				$article->date_end = $date;
				$article->end_action = $action;
				break;

			case 'remove-auto-pub':
				$article->date_published = null;
				break;

			case 'auto-unpub':
				$date = date_create('@' . $this->in->getUint('end_timestamp'));
				$article->date_published = $date;
				break;

			case 'add-related':
				$updater = new RelatedContentUpdate($article);
				$updater->addRelated(
					$this->in->getString('content_type'),
					$this->in->getString('content_id')
				);
				break;

			case 'remove-related':
				$updater = new RelatedContentUpdate($article);
				$updater->removeRelated(
					$this->in->getString('content_type'),
					$this->in->getString('content_id')
				);
				break;

			case 'remove-blob':

				foreach ($article->attachments as $k => $attach) {
					if ($attach->blob['id'] == $this->in->getUint('blob_id')) {
						$article->attachments->remove($k);
						App::getOrm()->remove($attach);
						break;
					}
				}

				break;

			case 'content':

				App::getOrm()->getRepository('DeskPRO:PersonPref')->deletePrefForPersonId('agent.ui.state.editarticle', $this->person->id);

				$article['content'] = $this->in->getString('content');

				$rev = ContentRevisionUtil::findOrCreate($article, 'content', $this->person);
				$rev['content'] = $article['content'];

				$glossary = new \Application\DeskPRO\Publish\GlossaryHandler($this->em);
				$content = $article->content;
				$content = $glossary->processText($content);

				$data['content_html'] = $this->renderView('AgentBundle:Kb:view-content-tab.html.twig', array(
					'article' => $article,
					'content' => $content
				));
				break;
		}

		$this->em->persist($article);
		if ($rev) {
			$this->em->persist($rev);
		}

		$this->em->flush();
		$this->em->commit();

		if ($rev) {
			$data['revision_id'] = $rev['id'];
		} else {
			$data['revision_id'] = null;
		}

		return $this->createJsonResponse($data);
	}

	public function ajaxSaveCommentAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);

		$comment = new ArticleComment();
		$comment->article = $article;
		$comment->person = $this->person;
		$comment['content'] = $this->in->getString('content');
		$comment['status'] = 'visible';
		$comment['date_created']  = new \DateTime();

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->render('AgentBundle:Kb:view-comment.html.twig', array(
			'comment' => $comment
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

		return $this->render('AgentBundle:Kb:pending-articles.html.twig', array(
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

		$row_html = $this->renderView('AgentBundle:Kb:pending-articles-page.html.twig', array('pending_articles' => array($pending_article)));

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

	public function pendingArticleInfoAction($pending_article_id)
	{
		$pending_article = App::findEntity('DeskPRO:ArticlePendingCreate', $pending_article_id);

		$data = array();
		$data['id'] = $pending_article_id;
		$data['comment'] = $pending_article['comment'];

		$data['person_id'] = $pending_article->person['id'];
		$data['person_name'] = $pending_article->person->getDisplayName();

		if ($pending_article->ticket) {
			$data['ticket_id'] = $pending_article->ticket->id;
			$data['ticket_subject'] = $pending_article->ticket->subject;
			$data['ticket_url'] = $this->get('router')->generate('agent_ticket_view', array('ticket_id' => $pending_article->ticket->id));
		}
		if ($pending_article->message) {
			$data['message_id'] = $pending_article->message->id;
			$data['message_content_html'] = $pending_article->message->getMessageHtml();
		}

		return $this->createJsonResponse($data);
	}

	public function pendingArticlesMassActionsAction($action)
	{
		$this->em->beginTransaction();

		$p_articles = $this->em->getRepository('DeskPRO:ArticlePendingCreate')->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

		foreach ($p_articles as $p_article) {
			switch ($action) {
				case 'delete':
					$this->em->remove($p_article);
					break;
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => 1
		));
	}

	############################################################################
	# Listings
	############################################################################

	public function listAction($category_id = 0)
	{
		$category = null;
		if ($category_id) {
			$category = App::findEntity('DeskPRO:ArticleCategory', $category_id);
		}

		$show_all = false;
		if (!$category) {
			$show_all = $this->in->getBool('all');
		}

		$result_helper = ArticleResults::newFromRequest($this, array(
			'category' => $category,
			'show_all' => $show_all
		));

		$page = $this->in->getUint('p');
		if (!$page) $page = 1;

		$results = $result_helper->getArticlesForPage($page);
		$result_cache = $result_helper->getResultCache();

		$display_fields = $this->person->getPref('agent.ui.kb-filter-display-fields.' . $result_cache['id']);
		if (!$display_fields) {
			$display_fields = array('author', 'date_created');
		}

		$tpl = 'AgentBundle:Kb:filter.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Kb:filter-page.html.twig';
		}

		return $this->render($tpl, array(
			'results'            => $results,
			'result_id'          => $result_cache['id'],
			'display_fields'     => $display_fields,

			'search_form'        => array('terms' => $result_cache['criteria']['terms']),
			'terms_summary'      => $result_cache['extra']['summary'],
			'category'           => $category,
		));
	}

	public function articleInfoAction($article_id)
	{
		$article = App::findEntity('DeskPRO:Article', $article_id);

		$data = array(
			'article_id' => $article['id'],
			'permalink'  => $article->getLink(),
			'content'    => $article->getContentPlain()
		);

		return $this->createJsonResponse($data);
	}

	############################################################################
	# Compare revisions
	############################################################################

	public function compareRevisionsAction($rev_old_id, $rev_new_id)
	{
		$diff_info = ContentRevisionUtil::compareRevisions('DeskPRO:ArticleRevision', $rev_old_id, $rev_new_id);

		return $this->render('AgentBundle:Kb:compare-revs.html.twig', array(
			'rev_old' => $rev_old,
			'rev_new' => $rev_new,
			'rendered_content_diff' => $diff_info['rendered_content_diff'],
			'rendered_title_diff'   => $diff_info['rendered_title_diff'],
		));
	}

	############################################################################
	# New article
	############################################################################

	public function newArticleAction()
	{
		$article_categories = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoryHelper()->getCategoriesInHierarchy();

		$state = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId('agent.ui.state.newarticle', $this->person->id);

		return $this->render('AgentBundle:Kb:newarticle.html.twig', array(
			'article_categories' => $article_categories,
			'state' => $state
		));
	}

	public function newArticleSaveAction()
	{
		$newarticle = new \Application\AgentBundle\Form\Model\NewArticle($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewArticle();
		$form = $this->get('form.factory')->create($formType, $newarticle);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			$newarticle->save();

			$article = $newarticle->getArticle();

			if ($this->in->getUint('pending_article_id')) {
				$pending_article = App::findEntity('DeskPRO:ArticlePendingCreate', $this->in->getUint('pending_article_id'));
				if ($pending_article) {
					App::getOrm()->remove($pending_article);
					App::getOrm()->flush();
				}
			}

			App::getOrm()->getRepository('DeskPRO:PersonPref')->deletePrefForPersonId('agent.ui.state.newarticle', $this->person->id);

			return $this->createJsonResponse(array(
				'success' => true,
				'article_id' => $article['id']
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}
}
