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
	protected function init()
	{
		parent::init();

		$this->person->loadHelper('IdeaVotes', array('visitor' => App::getSession()->getVisitor()));
	}

	
	/**
	 * Main index shows initial category listing
	 */
	public function filterAction($status = 'popular', $slug = 'all', $page = 1)
	{
		if (!$status) {
			$status = 'new';
		}

		if ($slug == 'all') {
			$slug = '';
		}
		
		if ($slug) {
			$category = App::getEntityRepository('DeskPRO:IdeaCategory')->getBySlug($slug);

			if (!$category) {
				return $this->renderStandardError('@core.error_page_not_found', '@core.not_found', 404);
			}

			// Auto-correct URL
			if ($slug != $category->getUrlSlug()) {
				return $this->redirectRoute('user_ideas', array('slug' => $category->getUrlSlug()), 301);
			}

			$category_path = $category->getTreeParents();

		} else {
			$category = null;
			$category_path = array();
		}

		$ideas = array();
		$idea_cats  = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$idea_cat_objs = App::getEntityRepository('DeskPRO:IdeaCategory')->getAll();

		$searcher = new \Application\DeskPRO\Searcher\IdeaSearch();

		if ($status == 'popular') {
			$searcher->addTerm('popular', 'is', 1);
		} else {
			$searcher->addTerm('status', 'is', $status);
		}

		if ($category) {
			$searcher->addTerm('category', 'is', $category['id']);
		}

		$search_options_url = '';
		$search_options = array();
		if ($this->in->getString('order_by')) {
			$searcher->setOrderByCode($this->in->getString('order_by'));
			$search_options_url = 'order_by=' . $this->in->getString('order_by');
			$search_options['order_by'] = $this->in->getString('order_by');
		}

		$total = $searcher->getCount();
		$pageinfo = Numbers::getPaginationPages($total, $page, 20, 3);
		$limit = array(
			'offset' => ($pageinfo['curpage']-1) * 20,
			'max' => 20
		);

		$idea_ids = $searcher->getMatches($limit);

		$ideas = App::getEntityRepository('DeskPRO:Idea')->getByResultIds($idea_ids);

		return $this->render('UserBundle:Ideas:filter.html.twig', array(
			'idea_cats'       => $idea_cats,
			'idea_cat_objs'   => $idea_cat_objs,
			'category'        => $category,
			'category_path'   => $category_path,
			'status'          => $status,
			'ideas'           => $ideas,
			'pageinfo'        => $pageinfo,
			'num_results'     => $total,
			'search_options' => $search_options,
			'search_options_url' => $search_options_url,
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

		if ($this->person['id']) {
			App::getDb()->delete('idea_votes', array('idea_id' => $idea['id'], 'person_id' => $this->person['id']));
		}

		App::getDb()->delete('idea_votes', array('idea_id' => $idea['id'], 'visitor_id' => App::getSession()->getVisitor()->getId()));

		$vote = new Entity\IdeaVote();
		$vote['idea'] = $idea;
		if ($this->person['id']) {
			$vote['person'] = $this->person;
		} else {
			$vote['visitor'] = App::getSession()->getVisitor();
		}
		$vote['num_votes'] = $this->in->getUint('vote');
		$vote['date_created'] = new \DateTime();

		App::getOrm()->persist($vote);
		App::getOrm()->flush();

		$idea->recountVotes();
		App::getOrm()->persist($idea);
		App::getOrm()->flush();

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