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
use Orb\Util\Numbers;

use Application\DeskPRO\Comments\NewCommentFormType;

use Application\UserBundle\Controller\Helper\ContentRating;
use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;

class NewsController extends AbstractController
{
	public function browseAction($slug = '', $page = 1, $list_type = 'list')
	{
		/** @var $structure \Application\DeskPRO\Publish\Structure */
		$structure = $this->container->getSystemService('publish_structure');

		if ($this->in->getUint('page')) {
			$page = $this->in->getUint('page');
		}
		if (!$page || $page < 1) $page = 1;

		$search_options = array();
		$search_options['order_by'] = $this->in->getString('order_by');

		if ($slug) {
			$category_id = $this->container->getRouter()->getIdFromSlug($slug);
			$category = null;

			if ($category_id && $structure->hasNewsCategory($category_id)) {
				$category = $structure->getNewsCategory($category_id);
			}

			if (!$category) {
				return $this->renderStandardError('@user.error_not_found_title', '@user.error_not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_news', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

			$searcher = new \Application\DeskPRO\Searcher\NewsSearch();
			$searcher->setPersonContext($this->person);
			$searcher->addTerm('category', 'is', $category['id']);

		} else {
			$category = null;
			$category_path = null;

			$searcher = new \Application\DeskPRO\Searcher\NewsSearch();
			$searcher->setPersonContext($this->person);
		}

		$news_cats = $structure->getNewsCategories();
		$news_cat_objs = $structure->getNewsCategories();

		if ($search_options['order_by']) {
			$searcher->setOrderByCode($search_options['order_by']);
		} else {
			$searcher->setOrderBy('id', 'desc');
		}

		$per_page = 20;
		if ($this->request->isPartialRequest() == 'portal') {
			$per_page = 2;
		}

		$tpl = 'UserBundle:News:browse-list.html.twig';
		if ($this->request->isPartialRequest() == 'portal') {
			$tpl = 'UserBundle:News:portal-display.html.twig';
		}
		if ($this->request->isPartialRequest() == 'more') {
			$tpl = 'UserBundle:News:browse-news-list.html.twig';
		}

		$total = $searcher->getCount();
		$pageinfo = Numbers::getPaginationPages($total, $page, $per_page, 3);
		$limit = array(
			'offset' => ($pageinfo['curpage']-1) * $per_page,
			'max' => $per_page
		);

		$news_ids = $searcher->getMatches($limit);

		$news = App::getEntityRepository('DeskPRO:News')->getByResultIds($news_ids);

		$show_more = false;
		if ($page < $pageinfo['last']) {
			$show_more = true;
		}

		$comment_counts = array();
		if ($news) {
			$comment_counts = App::getEntityRepository('DeskPRO:NewsCategory')
				->getCommentHelper()
				->countsOnCollection($news);
		}

		$category_counts = $structure->getNewsCategoryCounts($this->person);

		return $this->render($tpl, array(
			'news_cats' => $news_cats,
			'news_cat_objs' => $news_cat_objs,
			'category' => $category,
			'category_counts' => $category_counts,
			'category_path' => $category_path,
			'news_entries' => $news,
			'comment_counts' => $comment_counts,
			'num_results' => $total,
			'pageinfo' => $pageinfo,
			'list_type' => $list_type,
			'per_page' => $per_page,
			'show_more' => $show_more
		));
	}

	/**
	 * View a post
	 *
	 * @param  $post_id
	 */
	public function viewAction($slug)
	{
		$news = App::getEntityRepository('DeskPRO:News')->getBySlug($slug);
		if (!$news) {
			return $this->renderStandardError('@user_news.error_not_found', '@user.error_not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $news->getUrlSlug()) {
			return $this->redirectRoute('user_news_view', array('slug' => $news->getUrlSlug()), 301);
		}

		// Get the user subscription
		$subscription = false;
		if (!$this->person->isGuest()) {
			$subscription = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($news, $this->person);
			if ($subscription) {
				$subscription->touch();
				$this->em->persist($subscription);
				$this->em->flush();
			}
		}

		$categories = App::getEntityRepository('DeskPRO:NewsCategory')->getRootNodes();
		$category = $news->category;
		$category_path = $category->getTreeParents();

		$comments = null;
		$comments_widget = null;
		$comments_helper = Comments::create($news);
		if ($comments_helper) {
			$comments_widget = $comments_helper->getHtml();
		} else {
			$comments = App::getEntityRepository('DeskPRO:NewsComment')->getComments($news);
		}

		if (App::getSetting('core.facebook_like')) {
			$like_helper = FacebookLike::create($news);
			$facebook_like = $like_helper->getHtml();
		}

		$related_finder = new RelatedContentFinder($this->person, $news);
		$related_content = $related_finder->getRelatedEntities();

		$content_rating = new ContentRating($news, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);
		$rating = $content_rating->getRating();

		if ($rating_log_search_id = $content_rating->getSearchLogId()) {
			$this->session->set('news.' . $news['id'], $rating_log_search_id);
		} elseif ($this->session->has('news.' . $news['id'])) {
			$rating_log_search_id = $this->session->get('news.' . $news['id']);
		} else {
			$rating_log_search_id = 0;
		}

		$tpl = 'UserBundle:News:view.html.twig';
		if ($this->in->getString('_partial') == 'overlayWidget') {
			$tpl = 'UserBundle:News:view-overlay.html.twig';
		}

		return $this->render($tpl, array(
			'subscription' => $subscription,
			'rating' => $rating,
			'rating_log_search_id' => $rating_log_search_id,
			'news' => $news,
			'category_path' => $category_path,
			'category' => $category,
			'categories' => $categories,
			'comments' => $comments,
			'comments_widget' => $comments_widget,

			'facebook_like' => isset($facebook_like) ? $facebook_like : null,

			'related_content' => $related_content
		));
	}



	/**
	 * Submit a new comment
	 *
	 * @param  $post_id
	 */
	public function newCommentAction($post_id)
	{
		if ($this->container->getSetting('core.interact_require_login')) {
			return $this->forward('UserBundle:Login:index');
		}

		if (!$this->person->hasPerm('news.comment')) {
			return $this->renderLoginOrPermissionError();
		}

		$post = App::getEntityRepository('DeskPRO:News')->find($post_id);
		if (!$post) {
			return $this->renderStandardError('@user_news.error_not_found', '@user.error_not_found', 404);
		}

		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\NewsComment',
			$this->person,
			array('news' => $post)
		);

		$newcomment_formtype = new NewCommentFormType($this->person);
		$form = $this->get('form.factory')->create($newcomment_formtype, $new_comment);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$comment = $new_comment->save();

			if ($new_comment->require_login) {
				return $this->redirectRoute('user_newcomment_finishlogin', array(
					'comment_type' => 'news',
					'comment_id' => $comment->id,
				));
			}
		}

		return $this->redirectRoute('user_news_view', array(
			'slug' => $post->getUrlSlug()
		));
	}
}
