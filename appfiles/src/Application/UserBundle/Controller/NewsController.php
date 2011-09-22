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
use Orb\Util\Numbers;

use Application\UserBundle\Controller\Helper\ContentRating;
use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;

class NewsController extends AbstractController
{
	public function browseAction($slug = '', $page = 1, $list_type = 'list')
	{
		if ($this->in->getUint('page')) {
			$page = $this->in->getUint('page');
		}
		if (!$page || $page < 1) $page = 1;

		$search_options = array();
		$search_options['order_by'] = $this->in->getString('order_by');

		if ($slug) {
			$category = App::getEntityRepository('DeskPRO:NewsCategory')->getBySlug($slug);

			if (!$category) {
				return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_news', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

			$searcher = new \Application\DeskPRO\Searcher\NewsSearch();
			$searcher->addTerm('category', 'is', $category['id']);

		} else {
			$category = null;
			$category_path = null;

			$searcher = new \Application\DeskPRO\Searcher\NewsSearch();
		}

		$news_cats = App::getEntityRepository('DeskPRO:NewsCategory')->getCategoryHelper()->getFlatHierarchy();
		$news_cat_objs = App::getEntityRepository('DeskPRO:NewsCategory')->getAll();

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

		$category_counts = App::getEntityRepository('DeskPRO:NewsCategory')->getAllCounts($this->person);

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
		$post = App::getEntityRepository('DeskPRO:News')->getBySlug($slug);
		if (!$post) {
			return $this->renderStandardError('@user_news.error_not_found', '@core.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $post->getUrlSlug()) {
			return $this->redirectRoute('user_news_view', array('slug' => $post->getUrlSlug()), 301);
		}

		// Get the user subscription
		$subscription = false;
		if (!$this->person->isGuest()) {
			$subscription = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($post, $this->person);
			if ($subscription) {
				$subscription->touch();
				$this->em->persist($subscription);
				$this->em->flush();
			}
		}

		$categories = App::getEntityRepository('DeskPRO:NewsCategory')->getRootNodes();
		$category = $post->category;
		$category_path = $category->getTreeParents();

		$comments = null;
		$comments_widget = null;
		$comments_helper = Comments::create($post);
		if ($comments_helper) {
			$comments_widget = $comments_helper->getHtml();
		} else {
			$comments = App::getEntityRepository('DeskPRO:NewsComment')->getComments($post);
		}

		if (App::getSetting('core.facebook_like')) {
			$like_helper = FacebookLike::create($post);
			$facebook_like = $like_helper->getHtml();
		}

		$related_finder = new RelatedContentFinder($this->person, $post);
		$related_content = $related_finder->getRelatedEntities();

		$content_rating = new ContentRating($post, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);
		$rating = $content_rating->getRating();

		if ($rating_log_search_id = $content_rating->getSearchLogId()) {
			$this->session->set('news.' . $post['id'], $rating_log_search_id);
		} elseif ($this->session->has('news.' . $post['id'])) {
			$rating_log_search_id = $this->session->get('news.' . $post['id']);
		} else {
			$rating_log_search_id = 0;
		}

		return $this->render('UserBundle:News:view.html.twig', array(
			'subscription' => $subscription,
			'rating' => $rating,
			'rating_log_search_id' => $rating_log_search_id,
			'post' => $post,
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
		$post = App::getEntityRepository('DeskPRO:News')->find($post_id);
		if (!$post) {
			return $this->renderStandardError('@user_news.error_not_found', '@core.not_found', 404);
		}

		$form = new \Application\DeskPRO\Comments\CommentForm('new_comment', array('validator' => $this->get('validator')));
		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\NewsComment',
			array('news' => $post)
		);

		$form->bind($this->get('request'), $new_comment);

		if ($form->isValid()) {
			$comment = $new_comment->save();
		}

		return $this->redirectRoute('user_news_view', array(
			'post_id' => $post['id']
		));
	}
}
