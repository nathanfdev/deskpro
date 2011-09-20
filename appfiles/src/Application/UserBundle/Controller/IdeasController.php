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

use Application\UserBundle\Form\NewIdeaType;
use Application\DeskPRO\Comments\NewCommentFormType;

use Application\UserBundle\Controller\Helper\Comments;
use Application\UserBundle\Controller\Helper\FacebookLike;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;

class IdeasController extends AbstractController
{
	/**
	 * Main index shows initial category listing
	 */
	public function filterAction($status = 'popular', $slug = 'all')
	{
		$page = $this->in->getUint('page');
		$page = max(1, $page);
		$per_page = 20;

		$parent_status = $status;
		$sub_status_id = 0;

		if (!$status) {
			$status = 'new';
		}

		if (!$slug) {
			$slug = 'all';
		}

		$search_options = array(
			'order_by' => 'num_ratings',
		);

		if ($slug && $slug != 'all') {
			$category = App::getEntityRepository('DeskPRO:IdeaCategory')->getBySlug($slug);

			if (!$category) {
				return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
			}

			$cat_id = $category['id'];

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_ideas', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

		} else {
			$category = null;
			$cat_id = 0;
			$category_path = array();
		}

		$idea_cats  = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getCategoriesInHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();
		$status_subcats = Arrays::mergeAssoc($active_status_cats, $closed_status_cats);

		$searcher = new \Application\DeskPRO\Searcher\IdeaSearch();

		if ($status == 'popular') {
			$searcher->addTerm('status', 'is', array('active', 'new'));
			$searcher->setOrderByCode('num_ratings');
			$search_options['order_by'] = 'num_ratings';
		} else {
			if ($status != 'all') {
				$searcher->addTerm('status', 'is', $status);
			}

			if (strpos($status, '.') !== false) {
				list($parent_status, $sub_status_id) = explode('.', $status, 2);
			}

			if ($this->in->getString('order_by')) {
				$search_options['order_by'] = $this->in->getString('order_by');
				$searcher->setOrderByCode($search_options['order_by']);
			}
		}

		if ($category) {
			$searcher->addTerm('category', 'is', $category['id']);
		}

		$total = $searcher->getCount();
		$pageinfo = Numbers::getPaginationPages($total, $page, $per_page, 3);
		$limit = array(
			'offset' => ($pageinfo['curpage']-1) * $per_page,
			'max' => $per_page
		);

		$idea_ids = $searcher->getMatches($limit);

		$ideas = App::getEntityRepository('DeskPRO:Idea')->getByResultIds($idea_ids);

		$category_counts = App::getEntityRepository('DeskPRO:IdeaCategory')->getAllCounts($this->person);

		$num_votes        = $this->person->IdeaVotes->getVotesRemaining();
		$num_votes_remain = $this->person->IdeaVotes->getVotesRemaining();

		$has_voted_ids = $this->person->IdeaVotes->getVotesOnIdeas($idea_ids);

		return $this->render('UserBundle:Ideas:filter.html.twig', array(
			'idea_cats'       => $idea_cats,
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
			'status_subcats'    => $status_subcats,
			'sub_status_id'      => $sub_status_id,
			'category'        => $category,
			'cat_id'          => 0,
			'category_path'   => $category_path,
			'category_counts' => $category_counts,
			'status'          => $status,
			'parent_status'   => $parent_status,
			'ideas'           => $ideas,
			'pageinfo'        => $pageinfo,
			'num_results'     => $total,
			'search_options' => $search_options,
			'has_voted_ids' => $has_voted_ids,
			'num_votes' => $num_votes,
			'num_votes_remain' => $num_votes_remain,
		));
	}



	/**
	 * New idea
	 */
	public function newIdeaAction()
	{
		$newidea = new \Application\DeskPRO\Ideas\NewIdea(
			$this->person,
			App::getSession()->getVisitor()
		);

		// Initial value from coming from a category
		if ($this->in->getUint('category_id')) {
			$newidea->category_id = $this->in->getUint('category_id');
		}

		$num_votes        = $this->person->IdeaVotes->getVotesRemaining();
		$num_votes_remain = $this->person->IdeaVotes->getVotesRemaining();

		if (!$num_votes_remain) {
			die('you must have at least one vote to submit a new idea');
		}

		$form = $this->get('form.factory')->create(new NewIdeaType($num_votes_remain));

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$idea = $newidea->save();

				return $this->redirectRoute('user_ideas_view', array('slug' => $idea->getUrlSlug()));
			}
		}

		return $this->render('UserBundle:Ideas:new-idea.html.twig', array(
			'form' => $form->createView(),
			'num_votes' => $num_votes,
			'num_votes_remain' => $num_votes_remain,
		));
	}



	/**
	 * View an idea
	 *
	 * @param  $idea_id
	 */
	public function viewAction($slug)
	{
		$idea = App::getEntityRepository('DeskPRO:Idea')->getBySlug($slug);
		if (!$idea) {
			return $this->renderStandardError('@user_ideas.error_not_found', '@core.not_found', 404);
		}

		// Auto-correct URL
		if ($slug != $idea->getUrlSlug()) {
			return $this->redirectRoute('user_ideas_view', array('slug' => $idea->getUrlSlug()), 301);
		}

		// Get the user subscription
		$subscription = false;
		if (!$this->person->isGuest()) {
			$subscription = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($idea, $this->person);
			if ($subscription) {
				$subscription->touch();
				$this->em->persist($subscription);
				$this->em->flush();
			}
		}

		$categories = App::getEntityRepository('DeskPRO:IdeaCategory')->getRootNodes();

		$category = $idea->category;
		$category_path = $category->getTreeParents();

		$num_votes        = $this->person->IdeaVotes->getVotesRemaining();
		$num_votes_this   = $this->person->IdeaVotes->getVotesOnIdea($idea);
		$num_votes_remain = $this->person->IdeaVotes->getVotesRemaining();

		$spend_on_this = min($num_votes_remain+$num_votes_this, 3);

		$comments = null;
		$comments_widget = null;
		$comments_helper = Comments::create($idea);
		if ($comments_helper) {
			$comments_widget = $comments_helper->getHtml();
		} else {
			$comments = App::getEntityRepository('DeskPRO:IdeaComment')->getComments($idea);
		}

		if (App::getSetting('core.facebook_like')) {
			$like_helper = FacebookLike::create($idea);
			$facebook_like = $like_helper->getHtml();
		}

		$related_finder = new RelatedContentFinder($this->person, $idea);
		$related_content = $related_finder->getRelatedEntities();

		return $this->render('UserBundle:Ideas:view.html.twig', array(
			'subscription' => $subscription,

			'num_votes' => $num_votes,
			'num_votes_this' => $num_votes_this,
			'num_votes_remain' => $num_votes_remain,
			'spend_on_this' => $spend_on_this,

			'idea'          => $idea,
			'category_path' => $category_path,
			'category'      => $category,
			'categories'    => $categories,

			'comments' => $comments,
			'comments_widget' => $comments_widget,

			'facebook_like' => isset($facebook_like) ? $facebook_like : null,

			'related_content' => $related_content
		));
	}



	/**
	 * View an idea
	 *
	 * @param  $idea_id
	 */
	public function voteAction($idea_id)
	{
		$idea = App::getEntityRepository('DeskPRO:Idea')->find($idea_id);
		if (!$idea) {
			return $this->renderStandardError('@user_ideas.error_not_found', '@core.not_found', 404);
		}

		$num_votes_remain = $this->person->IdeaVotes->getVotesRemaining();

		if ($this->person['id']) {
			$r = App::getEntityRepository('DeskPRO:Rating')->getRatingByPersonOnObject('Idea', $idea_id, $this->person, $this->session->getVisitor());
		} else {
			$r = App::getEntityRepository('DeskPRO:Rating')->getRatingByPersonOnObject('Idea', $idea_id, null, $this->session->getVisitor());
		}

		if ($r) {
			$num_votes_remain++;
			$idea->removeRating($r);
			$this->em->remove($r);
			$this->em->flush($r);
		}

		if ($this->in->getInt('rating')) {
			$num_votes_remain--;
			$content_rating = new \Application\UserBundle\Controller\Helper\ContentRating($idea, $this->person, $this->session->getVisitor());
			$content_rating->setRequest($this->request);

			$this->em->beginTransaction();
			$content_rating->setRating(
				$this->in->getInt('rating'),
				$this->in->getUint('log_search_id')
			);
			$this->em->flush();
			$this->em->commit();
		}

		$num_votes_remain = Numbers::bound($num_votes_remain, 0,10);

		if ($this->request->isXmlHttpRequest()) {
			return $this->createJsonResponse(array(
				'success' => true,
				'voted' => $this->in->getInt('rating'),
				'total_rating' => $idea->total_rating,
				'num_votes_remain' => $num_votes_remain,
			));
		}

		return $this->redirectRoute('user_ideas_view', array('slug' => $idea->getUrlSlug()));
	}



	/**
	 * Submit a new comment
	 *
	 * @param  $article_id
	 */
	public function newCommentAction($idea_id)
	{
		$idea = App::getEntityRepository('DeskPRO:Idea')->find($idea_id);
		if (!$idea) {
			return $this->renderStandardError('@user_ideas.error_not_found', '@core.not_found', 404);
		}

		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\IdeaComment',
			$this->person,
			array('idea' => $idea)
		);

		$newcomment_formtype = new NewCommentFormType($this->person);
		$form = $this->get('form.factory')->create($newcomment_formtype, $new_comment);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$comment = $new_comment->save();
			}
		}
		return $this->redirectRoute('user_ideas_view', array(
			'slug' => $idea->getUrlSlug(),
		));
	}


	public function quickBrowserAction($status, $category_id = 0, $num = 10)
	{
		$category = null;
		if ($category_id) {
			$category = App::findEntity('DeskPRO:IdeaCategory', $category_id);
		}

		$ideas = App::getEntityRepository('DeskPRO:Idea')->getNewest($status, $num, $category);

		$vars = array(
			'category' => $category,
			'ideas' => $ideas
		);

		if ($this->request->isPartialRequest()) {
			return $this->render('UserBundle:Ideas:quick-browser-list.html.twig', $vars);
		} else {
			return $this->render('UserBundle:Ideas:quick-browser.html.twig', $vars);
		}
	}
}
