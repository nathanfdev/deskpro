<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Rating;
use Doctrine\ORM\EntityManager;
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

    public function rateContentUp(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, false);

        $this->em->persist($content_rating);
        $this->em->flush([$content_rating, $content]);

        return $content_rating;
    }

    public function rateContentDown(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, true);

        $this->em->persist($content_rating);
        $this->em->flush([$content_rating, $content]);

        return $content_rating;
    }

    public function updatePersistedOrCreateNewRating(ContentAbstract $content, $visitor_id, $person, $down = false)
    {
        if ($person && $content_rating = $this->findPersonRating($content, $person)) {
            $this->changeExistingRating($content, $content_rating, $down);

            return $content_rating;
        }

        if ($content_rating = $this->findVisitorRating($content, $visitor_id)) {
            if ($person) {
                // if there is no "person" rating, but there IS a visitor rating for this
                // visitor ID, then we just want to update the existing record.
                $content_rating->setPerson($person);
            }
            $this->changeExistingRating($content, $content_rating, $down);

            return $content_rating;
        }

        $content_rating = new Rating();
        $content_rating->setContentObject($content);
        $content_rating->setPerson($person);
        $content_rating->setVisitorId($visitor_id);
        $content_rating->setIpAddress($this->request_stack->getMasterRequest()->getClientIp());
        if ($down) {
            $content_rating->rateDown();
        } else {
            $content_rating->rateUp();
        }
        $content->addRating($content_rating);

        $this->em->persist($content_rating);

        return $content_rating;
    }

    /**
     * @param ContentAbstract $content
     * @param Person          $person
     *
     * @return Rating|null
     */
    public function findPersonRating(ContentAbstract $content, Person $person = null)
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
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->setParameter(3, $person)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return;
    }

    /**
     * @param ContentAbstract $content
     * @param $visitor_id
     *
     * @return Rating|null
     */
    public function findVisitorRating(ContentAbstract $content, $visitor_id)
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
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->setParameter(3, $visitor_id)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return;
    }

    public function getPersonRating(ContentAbstract $content, Person $person = null)
    {
        return $this->findPersonRating($content, $person);
    }

    /**
     * @param ContentAbstract $content
     * @param $content_rating
     * @param $down
     */
    private function changeExistingRating(ContentAbstract $content, Rating $content_rating, $down)
    {
        if ($down) {
            if ($content_rating->getRating() > 0) {
                $content->markRatingChangedNegatively();
            }
            $content_rating->rateDown();
        } else {
            if ($content_rating->getRating() < 0) {
                $content->markRatingChangedPositivly();
            }
            $content_rating->rateUp();
        }
    }

    public function ratingCounts(ContentAbstract $content)
    {
        $rating_counts = [];

        $rating_counts['total'] = $this->em->createQuery('
                SELECT COUNT(DISTINCT r.id)
                FROM DeskPRO:Rating r
                WHERE r.object_type = ?1 AND r.object_id = ?2
            ')
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->getSingleScalarResult();

        $rating_counts['positive'] = $this->em->createQuery('
                SELECT COUNT(DISTINCT r.id)
                FROM DeskPRO:Rating r
                WHERE r.object_type = ?1 AND r.object_id = ?2
                AND r.rating > 0
            ')
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->getSingleScalarResult();

        return $rating_counts;
    }
}
