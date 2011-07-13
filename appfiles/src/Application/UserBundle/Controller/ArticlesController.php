<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Numbers;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Comments\NewCommentFormType;

use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

class ArticlesController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function browseAction($slug = '', $page = '')
	{
		if ($slug) {
			$category = App::getEntityRepository('DeskPRO:ArticleCategory')->getBySlug($slug);
	
			if (!$category) {
				return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_articles', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();
			$category_children = $category->children;

			$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
			$searcher->addTerm('category', 'is', $category['id']);
			$searcher->addTerm('status', 'is', 'published');
			$searcher->setOrderBy('id', 'desc');

			$total = $searcher->getCount();
			$pageinfo = Numbers::getPaginationPages($total, $page, 20, 3);
			$limit = array(
				'offset' => ($pageinfo['curpage']-1) * 20,
				'max' => 20
			);

			$article_ids = $searcher->getMatches($limit);

			$articles = App::getEntityRepository('DeskPRO:Article')->getByResultIds($article_ids);

		} else {
			$category = null;
			$category_children = App::getEntityRepository('DeskPRO:ArticleCategory')->getRootNodes();
			$category_path = array();
			$articles = array();
		}

		$category_children_articles = App::getEntityRepository('DeskPRO:Article')->getNewestInNodes($category_children, 5);

		return $this->render('UserBundle:Articles:browse.html.twig', array(
			'category' => $category,
			'category_path' => $category_path,
			'category_children' => $category_children,
			'category_children_articles' => $category_children_articles,
			'articles' => $articles,
		));
	}


	public function filterAction($page = 1)
	{
		$kb_cats  = App::getEntityRepository('DeskPRO:ArticleCategory')->getUserCategoryHelper()->getFlatHierarchy();
		$products = App::getEntityRepository('DeskPRO:Product')->getCategoryHelper()->getFlatHierarchy();

		$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
		$searcher->addTerm('status', 'is', 'published');

		$search_options = array();
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
			'offset' => ($page-1) * 20,
			'max' => 20
		));

		if ($article_ids) {
			$articles = App::getEntityRepository('DeskPRO:Article')->getByResultIds($article_ids);
		} else {
			$articles = array();
		}

		$pageinfo = Numbers::getPaginationPages($total, $page, 20, 3);

		return $this->render('UserBundle:Articles:find.html.twig', array(
			'kb_cats' => $kb_cats,
			'products' => $products,
			'pageinfo' => $pageinfo,
			'search_options' => $search_options,
			'search_options_url' => http_build_query($search_options, null, '&amp;'),
			'articles' => $articles,
			'num_results' => $total,
		));
	}


	/**
	 * @param int $page
	 */
	public function recentAction($page = 1)
	{
		$page = max(1, $page);
		
		$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
		$searcher->addTerm('status', 'is', 'published');
		$searcher->setOrderBy('id', 'desc');

		$article_ids = $searcher->getMatches(array(
			'offset' => ($page-1) * 20,
			'max' => 20
		));

		if ($article_ids) {
			$articles = App::getEntityRepository('DeskPRO:Article')->getByResultIds($article_ids);
		} else {
			$articles = array();
		}

		$show_more = (count($article_ids) == 20);

		$tpl = 'UserBundle:Articles:recent.html.twig';
		if ($this->request->isPartialRequest() == 'more') {
			$tpl = 'UserBundle:Articles:recent-items.html.twig';
		}

		return $this->render($tpl, array(
			'articles' => $articles,
			'show_more' => $show_more,
			'page' => $page,
		));
	}


	/**
	 * @param int $page
	 */
	public function popularAction($page = 1)
	{
		$page = max(1, $page);

		$searcher = new \Application\DeskPRO\Searcher\ArticleSearch();
		$searcher->addTerm('status', 'is', 'published');
		$searcher->addTerm('popular', 'is', '1');
		$searcher->setOrderBy('view_count', 'desc');

		$article_ids = $searcher->getMatches(array(
			'offset' => ($page-1) * 20,
			'max' => 20
		));

		if ($article_ids) {
			$articles = App::getEntityRepository('DeskPRO:Article')->getByResultIds($article_ids);
		} else {
			$articles = array();
		}

		$show_more = (count($article_ids) == 20);

		$tpl = 'UserBundle:Articles:popular.html.twig';
		if ($this->request->isPartialRequest() == 'more') {
			$tpl = 'UserBundle:Articles:popular-items.html.twig';
		}

		return $this->render($tpl, array(
			'articles' => $articles,
			'show_more' => $show_more,
			'page' => $page,
		));
	}


	/**
	 * View an article listing
	 *
	 * @param  $article_id
	 */
	public function articleAction($slug)
	{
		$article = App::getEntityRepository('DeskPRO:Article')->getBySlug($slug);
		if (!$article) {
			return $this->renderStandardError('@user_articles.error_not_found', '@core.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $article->getUrlSlug()) {
			return $this->redirectRoute('user_articles_article', array('slug' => $article->getUrlSlug()), 301);
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
			$comments = App::getEntityRepository('DeskPRO:ArticleComment')->getComments($article);
		}

		if (App::getSetting('core.facebook_like')) {
			$like_helper = FacebookLike::create($article);
			$facebook_like = $like_helper->getHtml();
		}

		$rating_this = App::getDb()->fetchColumn("
			SELECT rating
			FROM article_ratings
			WHERE (person_id = ? OR visitor_id = ?) AND article_id = ?
		", array(Util::coalesce($this->person['id'], -1), App::getSession()->getVisitor()->getId(), $article['id']));

		$related_finder = new RelatedContentFinder($this->person, $article);
		$related_content = $related_finder->getRelatedEntities();

		return $this->render('UserBundle:Articles:article.html.twig', array(
			'article' => $article,
			'all_categories' => $all_categories,
			'comments' => $comments,
			'comments_widget' => $comments_widget,
			'facebook_like' => isset($facebook_like) ? $facebook_like : null,
			'rating_this' => $rating_this,

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
		$article = App::getEntityRepository('DeskPRO:Article')->find($article_id);
		if (!$article) {
			return $this->renderStandardError('@user_articles.error_not_found', '@core.not_found', 404);
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

			if ($form->isValid()) {
				$comment = $new_comment->save();
			}
		}

		return $this->redirectRoute('user_articles_article', array(
			'slug' => $article->getUrlSlug()
		));
	}


	/**
	 * Rate an article
	 *
	 * @param  $idea_id
	 */
	public function rateAction($article_id)
	{
		$article = App::getEntityRepository('DeskPRO:Article')->find($article_id);
		if (!$article) {
			return $this->renderStandardError('@user_articles.error_not_found', '@core.not_found', 404);
		}

		if ($this->person['id']) {
			App::getDb()->delete('article_ratings', array('article_id' => $article['id'], 'person_id' => $this->person['id']));
		}

		App::getDb()->delete('article_ratings', array('article_id' => $article['id'], 'visitor_id' => App::getSession()->getVisitor()->getId()));

		$rating = new Entity\ArticleRating();
		$rating['article'] = $article;
		if ($this->person['id']) {
			$rating['person'] = $this->person;
		} else {
			$rating['visitor'] = App::getSession()->getVisitor();
		}
		$rating['rating'] = $this->in->getInt('rating');
		$rating['date_created'] = new \DateTime();

		App::getOrm()->persist($rating);
		App::getOrm()->flush();

		return $this->redirectRoute('user_articles_article', array('article_id' => $article['id'], 'slug' => $article['slug']));
	}
}