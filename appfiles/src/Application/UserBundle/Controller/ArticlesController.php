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
		$cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoryHierarchy();

		return $this->render('UserBundle:Article:index.html.twig', array(
			'categories' => $cats,
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
		$parents = array();

		$p = $category['parent'];
		while ($p) {
			$parents[] = $p;
			$p = $p['parent'];
		}

		$subcategories = App::getEntityRepository('DeskPRO:ArticleCategory')->getCategoryHierarchy($category['id']);

		// Articles
		$articles = App::getEntityRepository('DeskPRO:Article')->getArticlesInCategory($category);

		return $this->render('UserBundle:Article:category.html.twig', array(
			'category' => $category,
			'parents' => $parents,
			'subcategories' => $subcategories,
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

		return $this->render('UserBundle:Article:article.html.twig', array(
			'article' => $article,
			'all_categories' => $all_categories,
		));
	}
}