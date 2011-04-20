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

use \Application\UserBundle\Form\NewIdeaForm;

use \Application\UserBundle\Controller\Helper\Comments;
use \Application\UserBundle\Controller\Helper\FacebookLike;

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
	public function indexAction($slug, $status, $sort = 'date')
	{
		$categories = App::getEntityRepository('DeskPRO:IdeaCategory')->getRootNodes();

		$category = null;
		$category_path = null;
		if ($slug) {
			$category = App::getEntityRepository('DeskPRO:IdeaCategory')->getBySlug($slug);
			if (!$category) die('invalid');

			$category_path = $category->getTreeParents();
		}

		$ideas = App::getEntityRepository('DeskPRO:Idea')->getIdeas(
			$status,
			$category,
			$sort,
			100
		);

		return $this->render('UserBundle:Ideas:index.html.twig', array(
			'categories'      => $categories,
			'category'        => $category,
			'category_path'   => $category_path,
			'status'          => $status,
			'ideas'           => $ideas
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

		$num_votes        = $this->person->IdeaVotes->getVotesRemaining();
		$num_votes_remain = $this->person->IdeaVotes->getVotesRemaining();

		if (!$num_votes_remain) {
			die('you must have at least one vote to submit a new idea');
		}

		$form = new NewIdeaForm('newidea', array(
			'votes_remain' => $num_votes_remain,
			'validator' => $this->get('validator')
		));

		$form->bind($this->get('request'), $newidea);

		// Initial value from coming from a category
		if ($this->in->getUint('category_id')) {
			$form->get('category_id')->setData($this->in->getUint('category_id'));
		}

		if ($form->isValid()) {
			$idea = $newidea->save();

			return $this->redirectRoute('user_ideas_view', array('slug' => $idea->getUrlSlug()));
		}

		return $this->render('UserBundle:Ideas:new-idea.html.twig', array(
			'form' => $form,
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
			die('invalid');
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
			die('invalid');
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
			die('invalid');
		}

		$form = new \Application\DeskPRO\Comments\CommentForm('new_comment', array('validator' => $this->get('validator')));
		$new_comment = new \Application\DeskPRO\Comments\NewComment(
			'Application\\DeskPRO\\Entity\\IdeaComment',
			array('idea' => $idea)
		);

		$form->bind($this->get('request'), $new_comment);

		if ($form->isValid()) {
			$comment = $new_comment->save();
		}

		return $this->redirectRoute('user_ideas_view', array(
			'idea_id' => $idea['id'],
		));
	}
}