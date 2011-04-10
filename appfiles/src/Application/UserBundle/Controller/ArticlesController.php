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

use \Application\UserBundle\Form\RegPersonForm;

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
	public function categoryAction($category_id)
	{
		$category = App::getEntityRepository('DeskPRO:ArticleCategory')->find($category_id);
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
	public function articleAction($article_id, $slug)
	{
		$article = App::getEntityRepository('DeskPRO:Article')->find($article_id);
		if (!$article) {
			die('invalid');
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

		$comments = App::getEntityRepository('DeskPRO:ArticleComment')->getComments($article);

		return $this->render('UserBundle:Articles:article.html.twig', array(
			'article' => $article,
			'all_categories' => $all_categories,
			'comments' => $comments
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
			die('invalid');
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
}