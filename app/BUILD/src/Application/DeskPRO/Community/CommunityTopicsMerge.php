<?php

/**
 * DeskPRO.
 *
 * @category Community
 */

namespace Application\DeskPRO\Community;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\EntityRepository\Rating as RatingRepository;
use Application\DeskPRO\People\PersonContextInterface;
use Doctrine\ORM\EntityManager;

/**
 * Handles merging of one community topic into the other.
 */
class CommunityTopicsMerge implements PersonContextInterface
{
    /**
     * @var Person
     */
    protected $person;

    /**
     * @var CommunityTopic
     */
    protected $communityTopic;

    /**
     * @var CommunityTopic
     */
    protected $otherCommunityTopic;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param Person         $person_performer
     * @param CommunityTopic $communityTopic      The base community topic, this is the one that will still exist at the end
     * @param CommunityTopic $otherCommunityTopic The other community topic, the one that will be merged into $communityTopic and then deleted
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Person $person_performer,
        CommunityTopic $communityTopic,
        CommunityTopic $otherCommunityTopic
    ) {
        $this->em = App::getOrm();

        $this->communityTopic      = $communityTopic;
        $this->otherCommunityTopic = $otherCommunityTopic;
        $this->setPersonContext($person_performer);

        if ($communityTopic->getId() == $otherCommunityTopic->getId()) {
            throw new \InvalidArgumentException('You cannot merge an community topic with itself');
        }
    }

    public function setPersonContext(Person $person)
    {
        $this->person = $person;
    }

    public function checkPersonPermission()
    {
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
            $this->em->persist($this->communityTopic);
            $this->em->flush();

            $this->em->remove($this->otherCommunityTopic);
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
        if (!$this->communityTopic->getForum() && $this->otherCommunityTopic->getForum()) {
            $this->communityTopic->setForum($this->otherCommunityTopic->getForum());
        }
        $this->communityTopic->setViewCount($this->communityTopic->getViewCount() + $this->otherCommunityTopic->getViewCount());
    }

    protected function mergeVotes()
    {
        /** @var RatingRepository $ratingRepository */
        $ratingRepository = App::getEntityRepository(Rating::class);
        $votes            = $ratingRepository->getRatingsFor('community_topic', $this->communityTopic->getId());
        $other_votes      = $ratingRepository->getRatingsFor('community_topic', $this->otherCommunityTopic->getId());

        $finished_votes = $votes;

        // Votes are ordered by id
        // Create a map of users and visitors so we can easily match conflicts
        $map_fn = function (&$map, $field) use ($votes) {
            foreach ($votes as $id => $v) {
                if (isset($v[$field]) && $v[$field]) {
                    $map[$v[$field]] = $id;
                }
            }
        };

        $map_votes_person  = [];
        $map_votes_visitor = [];

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
                $this->communityTopic->addRating($v);
                $this->em->persist($v);
                $finished_votes[] = $v;
            }
        }

        $this->communityTopic->recalculateVoteStats($finished_votes);
    }

    public function mergeComments()
    {
        foreach ($this->otherCommunityTopic->getComments() as $comment) {
            $comment->setTopic($this->communityTopic);
            $this->em->persist($comment);
        }
    }

    public function mergeDescription()
    {
        $comment = new CommunityTopicComment();

        $comment->setPerson($this->otherCommunityTopic->getPerson());
        $comment->setContent($this->otherCommunityTopic->getRealContent());
        $comment->setDateCreated($this->otherCommunityTopic->getDateCreated());

        $this->communityTopic->addComment($comment);

        $this->em->persist($comment);
    }
}
