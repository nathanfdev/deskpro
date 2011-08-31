<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Ideas;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\IdeaComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

/**
 * Handles merging of one idea into the other
 */
class IdeaMerge implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 */
	protected $idea;

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 */
	protected $other_idea;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @throws \InvalidArgumentException
	 * @param \Application\DeskPRO\Entity\Person $person_performer
	 * @param \Application\DeskPRO\Entity\Idea $idea         The base idea, this is the one that will still exist at the end
	 * @param \Application\DeskPRO\Entity\Idea $other_idea   The other idea, the one that will be merged into $idea and then deleted
	 */
	public function __construct(Person $person_performer, Idea $idea, Idea $other_idea)
	{
		$this->em = App::getOrm();

		$this->idea = $idea;
		$this->other_idea = $other_idea;
		$this->setPersonContext($person_performer);

		if ($idea == $other_idea) {
			throw new \InvalidArgumentException("You cannot merge an idea with itself");
		}
	}

	public function setPersonContext(Person $person)
	{
		$this->person = $person;
	}

	public function checkPersonPermission()
	{
		// todo
		return true;
	}

	public function merge()
	{
		if (!$this->checkPersonPermission()) {
			throw new \DomainException('User does not have permission to merge these tickets');
		}

		$this->em->beginTransaction();

		try {

			$this->mergeProps();
			$this->mergeVotes();
			$this->mergeComments();
			$this->mergeDescription();
			$this->em->persist($this->idea);
			$this->em->flush();

			$this->em->remove($this->other_idea);
			$this->em->flush();

			$this->em->commit();

		} catch (\Exception $e) {
			$this->em->rollback();

			throw $e;
		}

		return true;
	}

	protected function mergeProps()
	{
		if (!$this->idea->category && $this->other_idea->category) {
			$this->idea->category = $this->other_idea->category;
		}
		$this->idea->view_count = $this->idea->view_count + $this->other_idea->view_count;
	}

	protected function mergeVotes()
	{
		$votes = App::getEntityRepository('DeskPRO:Rating')->getRatingsFor('idea', $this->idea->id);
		$other_votes = App::getEntityRepository('DeskPRO:Rating')->getRatingsFor('idea', $this->other_idea->id);

		$finished_votes = $votes;

		// Votes are ordered by id
		// Create a map of users and visitors so we can easily match conflicts
		$map_fn = function(&$map, $field) use ($votes) {
			foreach ($votes as $id => $v) {
				if (isset($v[$field]) && $v[$field]) {
					$map[$v[$field]] = $id;
				}
			}
		};

		$map_votes_person  = array();
		$map_votes_visitor = array();

		$map_fn($map_votes_person, 'person_id');
		$map_fn($map_votes_visitor, 'visitor_id');

		// Now go over all other votes to add them or merge them
		foreach ($other_votes as $v) {
			if ($v['person_id'] && isset($map_votes_person[$v['person_id']])) {
				// person already voted
				$this->em->remove($v);
			} elseif ($v['visitor_id'] && isset($map_votes_visitor[$v['visitor_id']])) {
				// same visitor voted
				$this->em->remove($v);
			} else {
				// Move the vote over
				$this->idea->addRating($v);
				$this->em->persist($v);
				$finished_votes[] = $v;
			}
		}

		$this->idea->recalculateVoteStats($finished_votes);
	}

	public function mergeComments()
	{
		foreach ($this->other_idea->comments as $comment) {
			$comment->idea = $this->idea;
			$this->em->persist($comment);
		}
	}

	public function mergeDescription()
	{
		$comment = new IdeaComment();

		$comment->person = $this->other_idea->person;
		$comment->content = $this->other_idea->content;
		$comment->date_Created = $this->other_idea->date_created;

		$this->idea->addComment($comment);

		$this->em->persist($comment);
	}
}
