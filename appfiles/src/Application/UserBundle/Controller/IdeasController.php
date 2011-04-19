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

class IdeasController extends AbstractController
{
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

		// Get number of votes user has
		if ($this->person['id']) {
			$num_votes = App::getDb()->fetchColumn("
				SELECT SUM(num_votes)
				FROM idea_votes
				WHERE person_id = ? AND is_returned = 0
			", array($this->person['id']));

			$num_votes_this = App::getDb()->fetchColumn("
				SELECT num_votes
				FROM idea_votes
				WHERE person_id = ? AND idea_id = ?
			", array($this->person['id'], $idea['id']));
		} else {
			$num_votes = App::getDb()->fetchColumn("
				SELECT SUM(num_votes)
				FROM idea_votes
				WHERE visitor_id = ? AND is_returned = 0
			", array(App::getSession()->getVisitor()->getId()));

			$num_votes_this = App::getDb()->fetchColumn("
				SELECT num_votes
				FROM idea_votes
				WHERE visitor_id = ? AND idea_id = ?
			", array(App::getSession()->getVisitor()->getId(), $idea['id']));
		}

		if (!$num_votes) $num_votes = 0;
		if (!$num_votes_this) $num_votes_this = 0;

		$num_votes_remain = 10 - $num_votes;

		$spend_on_this = min($num_votes_remain+$num_votes_this, 3);

		$comments = App::getEntityRepository('DeskPRO:IdeaComment')->getComments($idea);

		return $this->render('UserBundle:Ideas:view.html.twig', array(
			'num_votes' => $num_votes,
			'num_votes_this' => $num_votes_this,
			'num_votes_remain' => $num_votes_remain,
			'spend_on_this' => $spend_on_this,

			'idea'          => $idea,
			'category_path' => $category_path,
			'category'      => $category,
			'categories'    => $categories,

			'comments' => $comments
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

		return $this->redirectRoute('user_ideas_view', array('idea_id' => $idea['id']));
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