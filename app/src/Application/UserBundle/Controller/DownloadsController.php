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
		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$search_options = array();
		$search_options['order_by'] = $this->in->getString('order_by');

		if ($slug) {
			$category_id = $this->container->getRouter()->getIdFromSlug($slug);
			$category = null;

			if ($category_id && $structure->hasDownloadCategory($category_id)) {
				$category = $structure->getDownloadCategory($category_id);
			}

			if (!$category) {
				return $this->renderStandardError('@user.error.not_found_title', '@user.error.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_downloads', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

			$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
			$searcher->setPersonContext($this->person);
			$searcher->addTerm('category', 'is', $category['id']);

		} else {
			$category = null;
			$category_path = null;

			$searcher = new \Application\DeskPRO\Searcher\DownloadSearch();
			$searcher->setPersonContext($this->person);
		}

		$category_counts = $structure->getDownloadCategoryCounts($this->person);

		$categories = $structure->getDownloadRootCategories();

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

		$comment_counts = array();
		if ($downloads) {
			$comment_counts = App::getEntityRepository('DeskPRO:DownloadCategory')
				->getCommentHelper()
				->countsOnCollection($downloads);
		}

		return $this->render('UserBundle:Downloads:browse.html.twig', array(
			'categories' => $categories,
			'category' => $category,
			'category_counts' => $category_counts,
			'category_path' => $category_path,
			'downloads' => $downloads,
			'comment_counts' => $comment_counts,
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
		$searcher->setPersonContext($this->person);
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
		$searcher->setPersonContext($this->person);
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
			return $this->renderStandardError('@user.error.downloads_not_found', '@user.error.not_found', 404);
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

		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');
		$download->category->structure_helper = $structure;

		$tpl = 'UserBundle:Downloads:file.html.twig';
		if ($this->in->getString('_partial') == 'overlayWidget') {
			$tpl = 'UserBundle:Downloads:file-overlay.html.twig';
		}

		return $this->render($tpl, array(
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
		if ($this->container->getSetting('core.interact_require_login')) {
			return $this->forward('UserBundle:Login:index');
		}

		if (!$this->person->hasPerm('downloads.comment')) {
			return $this->renderLoginOrPermissionError();
		}

		$download = App::getEntityRepository('DeskPRO:Download')->find($download_id);
		if (!$download) {
			return $this->renderStandardError('@user.error.downloads_not_found', '@user.error.not_found', 404);
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

				if ($new_comment->require_login) {
					return $this->redirectRoute('user_newcomment_finishlogin', array(
						'comment_type' => 'article',
						'comment_id' => $comment->id,
					));
				}
			}
		}
		return $this->redirectRoute('user_downloads_file', array(
			'slug' => $download->getUrlSlug(),
		));
	}
}
