<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Numbers;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Comments\NewCommentFormType;

use Application\UserBundle\Controller\Helper\ContentRating;
use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

class ArticlesController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function browseAction($slug = '')
	{
		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		$page = $this->in->getUint('page');
		$page = max(1, $page);

		$per_page = 25;

		if ($slug) {
			$category_id = $this->container->getRouter()->getIdFromSlug($slug);
			$category = null;

			if ($category_id && $structure->hasArticleCategory($category_id)) {
				$category = $structure->getArticleCategory($category_id);
			}

			if (!$category) {
				return $this->renderStandardError('@user.error.not_found_title', '@user.error.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_articles', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();
			$category_children = $category->getChildren();

			$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
			$searcher->setPersonContext($this->person);
			$searcher->addTerm('category_specific', 'is', $category['id']);
			$searcher->addTerm('status', 'is', 'published');
			$searcher->setOrderBy('id', 'desc');

			$total = $searcher->getCount();
			$pageinfo = Numbers::getPaginationPages($total, $page, $per_page, 3);
			$limit = array(
				'offset' => ($pageinfo['curpage']-1) * $per_page,
				'max' => $per_page
			);

			$article_ids = $searcher->getMatches($limit);

			$articles = $this->em->getRepository('DeskPRO:Article')->getByResultIds($article_ids);

		} else {
			$category = null;
			$category_children = $structure->getArticleRootCategories();
			$category_path = array();
			$articles = array();
		}

		$category_counts = $structure->getArticleCategoryCounts($this->person);

		$comment_counts = array();
		if ($articles) {
			$comment_counts = $this->em->getRepository('DeskPRO:ArticleCategory')
				->getCommentHelper()
				->countsOnCollection($articles);
		}

		$category_children_articles = $this->em->getRepository('DeskPRO:Article')->getNewestInNodes($category_children, 5, $this->person);

		$tpl = 'UserBundle:Articles:browse.html.twig';

		return $this->render($tpl, array(
			'category' => $category,
			'category_path' => $category_path,
			'category_children' => $category_children,
			'category_children_articles' => $category_children_articles,
			'category_counts' => $category_counts,
			'articles' => $articles,
			'comment_counts' => $comment_counts,
			'section_counts' => $this->em->getRepository('DeskPRO:Article')->getSectionCounts()
		));
	}


	public function filterAction()
	{
		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		$page = $this->in->getUint('page');
		$page = max(1, $page);

		$per_page = 20;

		$kb_cats  = $structure->getArticleRootCategories();
		$products = $this->em->getRepository('DeskPRO:Product')->getFlatHierarchy();

		$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
		$searcher->setPersonContext($this->person);
		$searcher->addTerm('status', 'is', 'published');

		$search_options = array();
		$search_options['order_by'] = '';
		$search_options['product_id'] = '';
		$search_options['category_id'] = '';

		if ($this->in->getString('order_by')) {
			$searcher->setOrderByCode($this->in->getString('order_by'));
			$search_options['order_by'] = $this->in->getString('order_by');
		}
		if ($this->in->getUint('category_id')) {
			$searcher->addTerm('category', 'is', $this->in->getUint('category_id'));
			$search_options['category_id'] = $this->in->getUint('category_id');
		}
		if ($this->in->getUint('product_id')) {
			$searcher->addTerm('product', 'is', $this->in->getUint('product_id'));
			$search_options['product_id'] = $this->in->getUint('product_id');
		}

		$total = $searcher->getCount();
		$article_ids = $searcher->getMatches(array(
			'offset' => ($page-1) * $per_page,
			'max' => $per_page
		));

		if ($article_ids) {
			$articles = $this->em->getRepository('DeskPRO:Article')->getByResultIds($article_ids);
		} else {
			$articles = array();
		}

		$pageinfo = Numbers::getPaginationPages($total, $page, $per_page, 3);

		return $this->render('UserBundle:Articles:find.html.twig', array(
			'kb_cats' => $kb_cats,
			'products' => $products,
			'pageinfo' => $pageinfo,
			'search_options' => $search_options,
			'search_options_url' => http_build_query($search_options, null, '&amp;'),
			'articles' => $articles,
			'num_results' => $total,
			'section_counts' => $this->em->getRepository('DeskPRO:Article')->getSectionCounts($this->person),
		));
	}


	/**
	 * View an article listing
	 *
	 * @param  $article_id
	 */
	public function articleAction($slug)
	{
		$article = $this->em->getRepository('DeskPRO:Article')->getBySlug($slug);
		if (!$article) {
			return $this->renderStandardError('@user.error.articles_not_found', '@user.error.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $article->getUrlSlug()) {
			return $this->redirectRoute('user_articles_article', array('slug' => $article->getUrlSlug()), 301);
		}

		// Get the user subscription
		$subscription = false;
		if (!$this->person->isGuest()) {
			$subscription = $this->em->getRepository('DeskPRO:ContentSubscription')->getSubscription($article, $this->person);
			if ($subscription) {
				$subscription->touch();
				$this->em->persist($subscription);
				$this->em->flush();
			}
		}

		$all_categories = array();
		foreach ($article['categories'] as $cat) {
			$cats = array();
			$cats[] = $cat;
			$p = $cat['parent'];
			while ($p) {
				$cats[] = $p;
				$p = $p['parent'];
			}

			$all_categories[$cat['id']] = array_reverse($cats);
		}

		$comments = null;
		$comments_widget = null;
		$comments_helper = Comments::create($article);
		if ($comments_helper) {
			$comments_widget = $comments_helper->getHtml();
		} else {
			$comments = $this->em->getRepository('DeskPRO:ArticleComment')->getDisplayComments($article, $this->person, $this->session->getVisitor());
		}

		if ($this->container->getSetting('core.facebook_like')) {
			$like_helper = FacebookLike::create($article);
			$facebook_like = $like_helper->getHtml();
		}

		$related_finder = new RelatedContentFinder($this->person, $article);
		$related_content = $related_finder->getRelatedEntities();

		$content_rating = new ContentRating($article, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);
		$rating = $content_rating->getRating();

		if ($rating_log_search_id = $content_rating->getSearchLogId()) {
			$this->session->set('article.' . $article['id'], $rating_log_search_id);
		} elseif ($this->session->has('article.' . $article['id'])) {
			$rating_log_search_id = $this->session->get('article.' . $article['id']);
		} else {
			$rating_log_search_id = 0;
		}

		$tpl = 'UserBundle:Articles:article.html.twig';
		if ($this->in->getString('_partial') == 'overlayWidget') {
			$tpl = 'UserBundle:Articles:article-overlay.html.twig';
		}

		$glossary = new \Application\DeskPRO\Publish\GlossaryHandler($this->em);
		$glossary_words = $glossary->findWords($article->content);
		$word_defs = $glossary->getWordDefs($glossary_words);

		$this->db->executeUpdate("UPDATE articles SET view_count = view_count + 1 WHERE id = ?", array($article->getId()));

		return $this->render($tpl, array(
			'subscription' => $subscription,
			'rating' => $rating,
			'rating_log_search_id' => $rating_log_search_id,

			'article' => $article,
			'glossary_words' => $glossary_words,
			'word_defs' => $word_defs,
			'all_categories' => $all_categories,
			'comments' => $comments,
			'comments_widget' => $comments_widget,
			'facebook_like' => isset($facebook_like) ? $facebook_like : null,

			'related_content' => $related_content
		));
	}


	/**
	 * Submit a new comment
	 *
	 * @param  $article_id
	 */
	public function newCommentAction($article_id)
	{
		if ($this->container->getSetting('core.interact_require_login')) {
			return $this->forward('UserBundle:Login:index');
		}

		if (!$this->person->hasPerm('articles.comment')) {
			return $this->renderLoginOrPermissionError();
		}

		$article = $this->em->getRepository('DeskPRO:Article')->find($article_id);
		if (!$article) {
			return $this->renderStandardError('@user.error.articles_not_found', '@user.error.not_found', 404);
		}

		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\ArticleComment',
			$this->person,
			array('article' => $article)
		);

		$newcomment_formtype = new NewCommentFormType($this->person);
		$form = $this->get('form.factory')->create($newcomment_formtype, $new_comment);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$comment = $new_comment->save();

			if ($new_comment->require_login) {
				$return_url = $this->generateUrl('user_newcomment_finishlogin', array(
					'comment_type' => 'article',
					'comment_id' => $comment->id,
				));
				return $this->redirectRoute('user_login', array('return' => $return_url));
			}
		}

		return $this->redirectRoute('user_articles_article', array(
			'slug' => $article->getUrlSlug()
		));
	}
}
