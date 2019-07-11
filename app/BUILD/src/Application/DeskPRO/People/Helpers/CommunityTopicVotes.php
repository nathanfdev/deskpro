<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use Orb\Helper\ShortCallableInterface;

/**
 * Helps figure out this users votes on community topic and how many votes remain.
 */
class CommunityTopicVotes implements ShortCallableInterface
{
    /**
     * @var Person
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
     * Number of votes cast on a specific community topic.
     *
     * @var array
     */
    protected $communityTopicVotes = [];

    /**
     * @param Person $person
     * @param array  $options
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
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'community_topic' #AND is_returned = 0
            ", [$this->person['id'], null]);
        } else {
            $num_votes = 0;
        }

        $this->num_votes           = $num_votes;
        $this->num_votes_remaining = max(0, 10 - $this->num_votes);

        return $this->num_votes;
    }

    /**
     * Get how many votes this user has cast on a specific community topic.
     *
     * @param CommunityTopic|int $communityTopic An CommunityTopic or an community topic ID
     *
     * @return int
     */
    public function getVotesOnCommunityTopic($communityTopic)
    {
        $communityTopicId = $communityTopic;
        if (is_object($communityTopicId) or is_array($communityTopicId)) {
            $communityTopicId = $communityTopicId['id'];
        }

        // Already know it
        if (isset($this->communityTopicVotes[$communityTopicId])) {
            return $this->communityTopicVotes[$communityTopicId];
        }

        if ($this->person['id']) {
            $num_votes_this = App::getDb()->fetchColumn("
                SELECT rating
                FROM ratings
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'community_topic' AND object_id = ?
            ", [$this->person['id'], null, $communityTopicId]);
        } elseif ($this->visitor) {
            $num_votes_this = App::getDb()->fetchColumn("
                SELECT rating
                FROM ratings
                WHERE visitor_id = ? AND object_type = 'community_topic' AND object_id = ?
            ", [$this->visitor['id'], $communityTopicId]);
        } else {
            $num_votes_this = 0;
        }

        $this->communityTopicVotes[$communityTopicId] = $num_votes_this;

        return $this->communityTopicVotes[$communityTopicId];
    }

    /**
     * Get vote status on a bunch of community topics.
     *
     * @param array $communityTopic
     *
     * @return array
     */
    public function getVotesOnCommunityTopicsCollection(array $communityTopic)
    {
        $ids = [];

        foreach ($communityTopic as $i) {
            if ($i instanceof CommunityTopic) {
                $ids[] = $i->getId();
            } else {
                $ids[] = (int) $i;
            }
        }

        if (!$ids) {
            return $this->communityTopicVotes;
        }

        if ($this->person['id']) {
            $vote_info = App::getDb()->fetchAllKeyValue("
                SELECT object_id, rating
                FROM ratings
                WHERE (person_id = ? OR visitor_id = ?) AND object_type = 'community_topic' AND object_id IN (?)
            ",
                [$this->person['id'], null, $ids],
                [\PDO::PARAM_INT, \PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        } elseif ($this->visitor) {
            $vote_info = App::getDb()->fetchAllKeyValue("
                SELECT object_id, rating
                FROM ratings
                WHERE visitor_id = ? AND object_type = 'community_topic' AND object_id IN (?)
            ",
                [null, $ids],
                [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
        } else {
            $vote_info = array_combine($ids, array_fill(0, count($ids), 0));
        }

        foreach ($vote_info as $k => $v) {
            $this->communityTopicVotes[$k] = $v;
        }

        return $this->communityTopicVotes;
    }

    public function getShortCallableNames()
    {
        return [
            'getCommunityTopicVotesRemaining' => 'getVotesRemaining',
            'getCommunityTopicVotesUsed'      => 'getVotesUsed',
            'CommunityTopicVotes'             => '_getthis',
        ];
    }

    public function _getthis()
    {
        return $this;
    }
}
