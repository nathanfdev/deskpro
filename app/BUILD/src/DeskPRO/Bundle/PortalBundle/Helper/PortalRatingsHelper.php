<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Rating;
use DeskPRO\Bundle\AppBundle\Model\RatingModel;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\RequestStack;

class PortalRatingsHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RequestStack
     */
    private $request_stack;

    public function __construct(EntityManager $em, RequestStack $request_stack)
    {
        $this->em            = $em;
        $this->request_stack = $request_stack;
    }

    public function rateContentUp(RatingModel $rating, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($rating, $visitor_id, $person, false);

        if ($content_rating) {
            $this->em->persist($content_rating);
            $this->em->flush([$content_rating, $rating->getObject()]);
        }

        return $content_rating;
    }

    public function rateContentDown(RatingModel $rating, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($rating, $visitor_id, $person, true);

        if ($content_rating) {
            $this->em->persist($content_rating);
            $this->em->flush([$content_rating, $rating->getObject()]);
        }

        return $content_rating;
    }

    /**
     * @param RatingModel $rating
     * @param Person|null $person
     * @param null $visitorId
     *
     * @throws OptimisticLockException
     *
     * @return ContentAbstract|false
     */
    public function removeContentRating(RatingModel $rating, Person $person = null, $visitorId = null)
    {
        $contentRating = $this->findPersonRating($rating, $person);
        if (!$contentRating) {
            $contentRating = $this->findVisitorRating($rating, $visitorId);
        }

        if (null === $contentRating) {
            return false;
        }

        $content = $rating->getObject();
        $rating->removeRating($contentRating);

        $this->em->remove($contentRating);
        $this->em->persist($content);
        $this->em->flush();

        return $content;
    }

    public function updatePersistedOrCreateNewRating(RatingModel $rating, $visitor_id, $person, $down = false)
    {
        $contentRating = $this->findPersonRating($rating, $person);
        if (!$contentRating) {
            $contentRating = $this->findVisitorRating($rating, $visitor_id);
        }

        //Already Upvoted
        if (null !== $contentRating && !$down) {
            return false;
        }

        if ($contentRating) {
            if ($person && null === $contentRating->getPerson()) {
                $contentRating->setPerson($person);
            }
            $this->changeExistingRating($rating, $contentRating, $down);

            return $contentRating;
        }

        $content_rating = new Rating();
        $content_rating->setContentObject($rating);
        $content_rating->setPerson($person);
        $content_rating->setVisitorId($visitor_id);
        $content_rating->setIpAddress($this->request_stack->getMasterRequest()->getClientIp());
        if ($down) {
            $content_rating->rateDown();
        } else {
            $content_rating->rateUp();
        }
        $rating->addRating($content_rating);

        $this->em->persist($content_rating);

        return $content_rating;
    }

    /**
     * @param RatingModel $rating
     * @param Person|null $person
     *
     * @return Rating|null
     */
    public function findPersonRating(RatingModel $rating, Person $person = null)
    {
        if (!$person) {
            return;
        }

        $res = $this->em->createQuery('
                SELECT r
                FROM DeskPRO:Rating r
                WHERE
                    r.object_type = ?1 AND r.object_id = ?2
                    AND (r.person = ?3)
            ')
            ->setParameter(1, $rating->getContentType())
            ->setParameter(2, $rating->getContentId())
            ->setParameter(3, $person)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return;
    }

    /**
     * @param RatingModel $rating
     * @param $visitor_id
     *
     * @return Rating|null
     */
    public function findVisitorRating(RatingModel $rating, $visitor_id)
    {
        if (!$visitor_id) {
            return;
        }

        $res = $this->em->createQuery('
                SELECT r
                FROM DeskPRO:Rating r
                WHERE
                    r.object_type = ?1 AND r.object_id = ?2
                    AND (r.visitor_id = ?3)
            ')
            ->setParameter(1, $rating->getContentType())
            ->setParameter(2, $rating->getContentId())
            ->setParameter(3, $visitor_id)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return;
    }

    public function getPersonRating(RatingModel $rating, Person $person = null)
    {
        return $this->findPersonRating($rating, $person);
    }

    /**
     * @param RatingModel $rating
     * @param Rating $content_rating
     * @param $down
     */
    private function changeExistingRating(RatingModel $rating, Rating $content_rating, $down)
    {
        if ($down) {
            if ($content_rating->getRating() > 0) {
                $rating->markRatingChangedNegatively();
            }
            $content_rating->rateDown();
        } else {
            if ($content_rating->getRating() < 0) {
                $rating->markRatingChangedPositivly();
            }
            $content_rating->rateUp();
        }
    }

    public function ratingCounts(RatingModel $rating)
    {
        $rating_counts = [];

        $rating_counts['total'] = $this->em->createQuery('
                SELECT COUNT(DISTINCT r.id)
                FROM DeskPRO:Rating r
                WHERE r.object_type = ?1 AND r.object_id = ?2
            ')
            ->setParameter(1, $rating->getContentType())
            ->setParameter(2, $rating->getContentId())
            ->getSingleScalarResult();

        $rating_counts['positive'] = $this->em->createQuery('
                SELECT COUNT(DISTINCT r.id)
                FROM DeskPRO:Rating r
                WHERE r.object_type = ?1 AND r.object_id = ?2
                AND r.rating > 0
            ')
            ->setParameter(1, $rating->getContentType())
            ->setParameter(2, $rating->getContentId())
            ->getSingleScalarResult();

        return $rating_counts;
    }
}
