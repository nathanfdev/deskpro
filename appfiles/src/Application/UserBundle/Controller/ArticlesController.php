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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;
use \Orb\Util\Util;

use \Application\DeskPRO\ContentSearch\RelatedContentFinder;

use \Application\UserBundle\Controller\Helper\Comments;
use \Application\UserBundle\Controller\Helper\FacebookLike;

class ArticlesController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function indexAction()
	{
		$cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getRootNodes();

		$newest_cat_articles = App::getEntityRepository('DeskPRO:Article')->getNewestInNodes($cats);
		$newest_articles     = App::getEntityRepository('DeskPRO:Article')->getNewest();
		$top_rated_articles  = App::getEntityRepository('DeskPRO:Article')->getTopRated();

		return $this->render('UserBundle:Articles:index.html.twig', array(
			'categories'          => $cats,
			'newest_cat_articles' => $newest_cat_articles,
			'newest_articles'     => $newest_articles,
			'top_rated_articles'  => $top_rated_articles
		));
	}

	

	/**
	 * View a category listing
	 * 
	 * @param  $category_id
	 */
	public function categoryAction($slug)
	{
		$category = App::getEntityRepository('DeskPRO:ArticleCategory')->getBySlug($slug);

		if (!$category) {
			return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
		}

		$category_path = $category->getTreeParents();

		$articles = App::getEntityRepository('DeskPRO:Article')->getInNode($category);

		return $this->render('UserBundle:Articles:category.html.twig', array(
			'category' => $category,
			'category_path' => $category_path,
			'articles' => $articles
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

		$form = new \Application\DeskPRO\Comments\CommentForm('new_comment', array('validator' => $this->get('validator')));
		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\ArticleComment',
			array('article' => $article)
		);

		$form->bind($this->get('request'), $new_comment);

		if ($form->isValid()) {
			$comment = $new_comment->save();
		}

		return $this->redirectRoute('user_articles_article', array(
			'article_id' => $article['id'],
			'slug' => $article['slug']
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