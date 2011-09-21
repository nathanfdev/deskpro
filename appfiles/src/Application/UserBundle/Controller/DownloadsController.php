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
use Application\DeskPRO\Comments\NewCommentFormType;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\UserBundle\Controller\Helper\ContentRating;
use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

class DownloadsController extends AbstractController
{
	public function browseAction($slug = '')
	{
		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$search_options = array();
		$search_options['order_by'] = $this->in->getString('order_by');

		if ($slug) {
			$category = App::getEntityRepository('DeskPRO:DownloadCategory')->getBySlug($slug);

			if (!$category) {
				return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_downloads', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

			$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
			$searcher->addTerm('category', 'is', $category['id']);

		} else {
			$category = null;
			$category_path = null;

			$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
		}

		$category_counts = App::getEntityRepository('DeskPRO:DownloadCategory')->getAllCounts($this->person);

		$categories = App::getEntityRepository('DeskPRO:DownloadCategory')->getRootNodes();

		if ($search_options['order_by']) {
			$searcher->setOrderByCode($search_options['order_by']);
		} else {
			$searcher->setOrderBy('id', 'desc');
		}

		$total = $searcher->getCount();

		$per_page = 20;
		if ($this->request->isPartialRequest() == 'portal') {
			$per_page = 5;
		}

		$pageinfo = Numbers::getPaginationPages($total, $page, $per_page, 3);
		$limit = array(
			'offset' => ($pageinfo['curpage']-1) * $per_page,
			'max' => $per_page
		);

		$download_ids = $searcher->getMatches($limit);

		$downloads = App::getEntityRepository('DeskPRO:Download')->getByResultIds($download_ids);

		return $this->render('UserBundle:Downloads:browse.html.twig', array(
			'categories' => $categories,
			'category' => $category,
			'category_counts' => $category_counts,
			'category_path' => $category_path,
			'downloads' => $downloads,
			'num_results' => $total,
			'pageinfo' => $pageinfo,
			'section_counts' => App::getEntityRepository('DeskPRO:Download')->getSectionCounts($this->person),
		));
	}


	/**
	 * @param int $page
	 */
	public function recentAction($page = 1)
	{
		$page = max(1, $page);

		$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
		$searcher->setOrderBy('id', 'desc');

		$download_ids = $searcher->getMatches(array(
			'offset' => ($page-1) * 20,
			'max' => 20
		));

		if ($download_ids) {
			$downloads = App::getEntityRepository('DeskPRO:Download')->getByResultIds($download_ids);
		} else {
			$downloads = array();
		}

		$show_more = (count($download_ids) == 20);

		$tpl = 'UserBundle:Downloads:recent.html.twig';
		if ($this->request->isPartialRequest() == 'more') {
			$tpl = 'UserBundle:Downloads:recent-items.html.twig';
		}

		return $this->render($tpl, array(
			'downloads' => $downloads,
			'show_more' => $show_more,
			'page' => $page,
			'section_counts' => App::getEntityRepository('DeskPRO:Download')->getSectionCounts($this->person),
		));
	}


	/**
	 * @param int $page
	 */
	public function popularAction($page = 1)
	{
		$page = max(1, $page);

		$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
		$searcher->addTerm('popular', 'is', '1');
		$searcher->setOrderBy('num_downloads', 'desc');

		$download_ids = $searcher->getMatches(array(
			'offset' => ($page-1) * 20,
			'max' => 20
		));

		if ($download_ids) {
			$downloads = App::getEntityRepository('DeskPRO:Download')->getByResultIds($download_ids);
		} else {
			$downloads = array();
		}

		$show_more = (count($download_ids) == 20);

		$tpl = 'UserBundle:Downloads:popular.html.twig';
		if ($this->request->isPartialRequest() == 'more') {
			$tpl = 'UserBundle:Downloads:popular-items.html.twig';
		}

		return $this->render($tpl, array(
			'downloads' => $downloads,
			'show_more' => $show_more,
			'page' => $page,
			'section_counts' => App::getEntityRepository('DeskPRO:Download')->getSectionCounts($this->person),
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

		// Get the user subscription
		$subscription = false;
		if (!$this->person->isGuest()) {
			$subscription = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($download, $this->person);
			if ($subscription) {
				$subscription->touch();
				$this->em->persist($subscription);
				$this->em->flush();
			}
		}

		$category = $download->category;
		$category_path = $category->getTreeParents();

		$related_finder = new RelatedContentFinder($this->person, $download);
		$related_content = $related_finder->getRelatedEntities();

		$comments = null;
		$comments_widget = null;
		$comments_helper = Comments::create($download);
		if ($comments_helper) {
			$comments_widget = $comments_helper->getHtml();
		} else {
			$comments = App::getEntityRepository('DeskPRO:DownloadComment')->getComments($download);
		}

		$content_rating = new ContentRating($download, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);
		$rating = $content_rating->getRating();

		if ($rating_log_search_id = $content_rating->getSearchLogId()) {
			$this->session->set('download.' . $download['id'], $rating_log_search_id);
		} elseif ($this->session->has('download.' . $download['id'])) {
			$rating_log_search_id = $this->session->get('download.' . $download['id']);
		} else {
			$rating_log_search_id = 0;
		}

		return $this->render('UserBundle:Downloads:file.html.twig', array(
			'subscription' => $subscription,
			'rating' => $rating,
			'rating_log_search_id' => $rating_log_search_id,

			'comments_widget' => $comments_widget,
			'comments' => $comments,

			'download' => $download,
			'category_path' => $category_path,

			'related_content' => $related_content
		));
	}


	/**
	 * Submit a new comment
	 *
	 * @param  $download_id
	 */
	public function newCommentAction($download_id)
	{
		$download = App::getEntityRepository('DeskPRO:Download')->find($download_id);
		if (!$download) {
			return $this->renderStandardError('@user_downloads.error_not_found', '@core.not_found', 404);
		}

		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\DownloadComment',
			$this->person,
			array('download' => $download)
		);

		$newcomment_formtype = new NewCommentFormType($this->person);
		$form = $this->get('form.factory')->create($newcomment_formtype, $new_comment);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$comment = $new_comment->save();
			}
		}
		return $this->redirectRoute('user_downloads_file', array(
			'slug' => $download->getUrlSlug(),
		));
	}
}
