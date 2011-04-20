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

class DownloadsController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function indexAction()
	{
		$cats = App::getEntityRepository('DeskPRO:DownloadCategory')->getRootNodes();

		$newest_downloads   = App::getEntityRepository('DeskPRO:Download')->getNewest();
		$popular_downloads  = App::getEntityRepository('DeskPRO:Download')->getPopular();

		return $this->render('UserBundle:Downloads:index.html.twig', array(
			'categories'        => $cats,
			'newest_downloads'  => $newest_downloads,
			'popular_downloads' => $popular_downloads
		));
	}

	

	/**
	 * View a category listing
	 * 
	 * @param  $category_id
	 */
	public function categoryAction($slug)
	{
		$category = App::getEntityRepository('DeskPRO:DownloadCategory')->getBySlug($slug);

		if (!$category) {
			return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $category->getUrlSlug()) {
			return $this->redirectRoute('user_downloads_cat', array('slug' => $category->getUrlSlug()), 301);
		}

		$category_path = $category->getTreeParents();

		$downloads = App::getEntityRepository('DeskPRO:Download')->getInNode($category);

		return $this->render('UserBundle:Downloads:category.html.twig', array(
			'category' => $category,
			'category_path' => $category_path,
			'downloads' => $downloads
		));
	}


	
	/**
	 * View a file
	 *
	 * @param  $article_id
	 */
	public function fileAction($slug)
	{
		$download = App::getEntityRepository('DeskPRO:Download')->getBySlug($slug);
		if (!$download) {
			return $this->renderStandardError('@user_downloads.not_found', '@core.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $download->getUrlSlug()) {
			return $this->redirectRoute('user_downloads_file', array('slug' => $download->getUrlSlug()), 301);
		}

		$category = $download->category;
		$category_path = $category->getTreeParents();

		return $this->render('UserBundle:Downloads:file.html.twig', array(
			'download' => $download,
			'category_path' => $category_path,
		));
	}
}