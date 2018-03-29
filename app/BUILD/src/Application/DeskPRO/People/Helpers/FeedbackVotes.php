<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;

/**
 * Helps figure out this users votes on feedback and how many votes remain.
 */
class FeedbackVotes implements \Orb\Helper\ShortCallableInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var int
     */
    protected $num_votes = null;

    /**
     * @var int
     */
    protected $num_votes_remaining = null;

    /**
     * Number of votes cast on specific feedback.
     *
     * @var array
     */
    protected $feedback_votes = [];

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     * @param array                              $options
     */
    public function __construct(Person $person, array $options)
    {
        $this->person = $person;
    }

    /**
     * Get how many votes the user has left to cast.
     *
     * @return int
     */
    public function getVotesRemaining()
    {
        $this->getVotesUsed();

        return $this->num_votes_remaining;
    }

    /**
     * Get how many votes the user has used.
     *
     * @return int
     */
    public function getVotesUsed()
    {
        if ($this->num_votes !== null) {
            return $this->num_votes;
        }

        if ($this->person['id']) {
            $num_votes = App::getDb()->fetchColumn("
                SELECT SUM(rating)
                FROM ratings
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' #AND is_returned = 0
            ", [$this->person['id'], null]);
        } else {
            $num_votes = 0;
        }

        $this->num_votes           = $num_votes;
        $this->num_votes_remaining = max(0, 10 - $this->num_votes);

        return $this->num_votes;
    }

    /**
     * Get how many votes this user has cast on a specific feedback.
     *
     * @param Feedback|int $feedback An Feedback or an feedback ID
     *
     * @return int
     */
    public function getVotesOnFeedback($feedback)
    {
        $feedback_id = $feedback;
        if (is_object($feedback_id) or is_array($feedback_id)) {
            $feedback_id = $feedback_id['id'];
        }

        // Already know it
        if (isset($this->feedback_votes[$feedback_id])) {
            return $this->feedback_votes[$feedback_id];
        }

        if ($this->person['id']) {
            $num_votes_this = App::getDb()->fetchColumn("
                SELECT rating
                FROM ratings
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' AND object_id = ?
            ", [$this->person['id'], null, $feedback_id]);
        } elseif ($this->visitor) {
            $num_votes_this = App::getDb()->fetchColumn("
                SELECT rating
                FROM ratings
                WHERE visitor_id = ? AND object_type = 'feedback' AND object_id = ?
            ", [$this->visitor['id'], $feedback_id]);
        } else {
            $num_votes_this = 0;
        }

        $this->feedback_votes[$feedback_id] = $num_votes_this;

        return $this->feedback_votes[$feedback_id];
    }

    /**
     * Get vote status on a bunch of feedback.
     *
     * @param array $feedback
     *
     * @return array
     */
    public function getVotesOnFeedbackCollection(array $feedback)
    {
        $ids = [];

        foreach ($feedback as $i) {
            if ($i instanceof \Application\DeskPRO\Entity\Feedback) {
                $ids[] = $i->getId();
            } else {
                $ids[] = (int) $i;
            }
        }

        if (!$ids) {
            return $this->feedback_votes;
        }

        if ($this->person['id']) {
            $vote_info = App::getDb()->fetchAllKeyValue("
                SELECT object_id, rating
                FROM ratings
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'feedback' AND object_id IN (?)
            ",
            [$this->person['id'], null, $ids],
            [\PDO::PARAM_INT, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        } elseif ($this->visitor) {
            $vote_info = App::getDb()->fetchAllKeyValue("
                SELECT object_id, rating
                FROM ratings
                WHERE visitor_id = ? AND object_type = 'feedback' AND object_id IN (?)
            ",
            [null, $ids],
            [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
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
        return [
            'getFeedbackVotesRemaining' => 'getVotesRemaining',
            'getFeedbackVotesUsed'      => 'getVotesUsed',
            'FeedbackVotes'             => '_getthis',
        ];
    }

    public function _getthis()
    {
        return $this;
    }
}
