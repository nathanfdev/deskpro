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

class NewsController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function indexAction($category_id)
	{
		$categories = App::getEntityRepository('DeskPRO:NewsCategory')->getRootNodes();

		$category = null;
		$category_path = null;
		if ($category_id) {
			$category = App::getEntityRepository('DeskPRO:NewsCategory')->find($category_id);
			if (!$category) die('invalid');

			$category_path = $category->getTreeParents();
		}

		$posts = App::getEntityRepository('DeskPRO:News')->getNews($category);

		return $this->render('UserBundle:News:index.html.twig', array(
			'categories'      => $categories,
			'category'        => $category,
			'category_path'   => $category_path,
			'posts'           => $posts
		));
	}

	

	/**
	 * View a post
	 *
	 * @param  $post_id
	 */
	public function viewAction($post_id)
	{
		$post = App::getEntityRepository('DeskPRO:News')->find($post_id);
		if (!$post) {
			die('invalid');
		}

		$categories = App::getEntityRepository('DeskPRO:NewsCategory')->getRootNodes();
		$category = $post->category;
		$category_path = $category->getTreeParents();

		return $this->render('UserBundle:News:view.html.twig', array(
			'post' => $post,
			'category_path' => $category_path,
			'category' => $category,
			'categories' => $categories,
		));
	}
}