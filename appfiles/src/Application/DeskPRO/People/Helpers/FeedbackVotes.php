<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PermissionCache;

use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Helps figure out this users votes on feedback and how many votes remain
 */
class IdeaVotes implements \Orb\Helper\ShortCallableInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * This is set if we're a session, we might be fetching votes based on
	 * visitor id.
	 *
	 * @var \Application\DeskPRO\Entity\Visitor
	 */
	protected $visitor;

	/**
	 * @var int
	 */
	protected $num_votes = null;

	/**
	 * @var int
	 */
	protected $num_votes_remaining = null;

	/**
	 * Number of votes cast on specific feedback
	 * @var array
	 */
	protected $feedback_votes = array();

	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param array $options
	 */
	public function __construct(Person $person, array $options)
	{
		$this->person = $person;

		if (!empty($options['visitor'])) {
			$this->visitor = $options['visitor'];
		}
	}



	/**
	 * Get how many votes the user has left to cast
	 *
	 * @return int
	 */
	public function getVotesRemaining()
	{
		$this->getVotesUsed();

		return $this->num_votes_remaining;
	}



	/**
	 * Get how many votes the user has used
	 *
	 * @return int
	 */
	public function getVotesUsed()
	{
		if ($this->num_votes !== null) return $this->num_votes;

		if ($this->person['id']) {
			$num_votes = App::getDb()->fetchColumn("
				SELECT SUM(rating)
				FROM ratings
				WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' #AND is_returned = 0
			", array($this->person['id'], $this->visitor['id']));
		} elseif ($this->visitor) {
			$num_votes = App::getDb()->fetchColumn("
				SELECT SUM(rating)
				FROM ratings
				WHERE visitor_id = ? AND object_type = 'feedback' #AND is_returned = 0
			", array(App::getSession()->getVisitor()->getId()));
		} else {
			$num_votes = 0;
		}

		$this->num_votes = $num_votes;
		$this->num_votes_remaining = max(0, 10 - $this->num_votes);

		return $this->num_votes;
	}



	/**
	 * Get how many votes this user has cast on a specific feedback
	 *
	 * @param Idea|int $feedback An Idea or an feedback ID
	 * @return int
	 */
	public function getVotesOnIdea($feedback)
	{
		$feedback_id = $feedback;
		if (is_object($feedback_id) OR is_array($feedback_id)) {
			$feedback_id = $feedback_id['id'];
		}

		// Already know it
		if (isset($this->feedback_votes[$feedback_id])) return $this->feedback_votes[$feedback_id];

		if ($this->person['id']) {
			$num_votes_this = App::getDb()->fetchColumn("
				SELECT rating
				FROM ratings
				WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' AND object_id = ?
			", array($this->person['id'], $this->visitor['id'], $feedback_id));
		} elseif ($this->visitor) {
			$num_votes_this = App::getDb()->fetchColumn("
				SELECT rating
				FROM ratings
				WHERE visitor_id = ? AND object_type = 'feedback' AND object_id = ?
			", array($this->visitor['id'], $feedback_id));
		} else {
			$num_votes_this = 0;
		}

		$this->feedback_votes[$feedback_id] = $num_votes_this;

		return $this->feedback_votes[$feedback_id];
	}


	/**
	 * Get vote status on a bunch of feedback
	 *
	 * @param array $feedback
	 * @return array
	 */
	public function getVotesOnIdeas(array $feedback)
	{
		$ids = array();

		foreach ($feedback as $i) {
			if ($i instanceof \Application\DeskPRO\Entity\Idea) {
				$ids[] = $i->getId();
			} else {
				$ids[] = (int)$i;
			}
		}

		if (!$ids) {
			return $this->feedback_votes;
		}


		$ids_in = implode(',', $ids);

		if ($this->person['id']) {
			$vote_info = App::getDb()->fetchAllKeyValue("
				SELECT object_id, rating
				FROM ratings
				WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' AND object_id IN ($ids_in)
			", array($this->person['id'], $this->visitor['id']));
		} elseif ($this->visitor) {
			$vote_info = App::getDb()->fetchAllKeyValue("
				SELECT object_id, rating
				FROM ratings
				WHERE visitor_id = ? AND object_type = 'feedback' AND object_id IN ($ids_in)
			", array($this->visitor['id']));
		} else {
			$vote_info = array_combine($ids, array_fill(0, count($ids), 0));
		}

		foreach ($vote_info as $k => $v) {
			$this->feedback_votes[$k] = $v;
		}

		return $this->feedback_votes;
	}



	public function getShortCallableNames()
	{
		return array(
			'getIdeaVotesRemaining' => 'getVotesRemaining',
			'getIdeaVotesUsed'      => 'getVotesUsed',
			'IdeaVotes'             => '_getthis',
		);
	}

	public function _getthis() { return $this; }
}
