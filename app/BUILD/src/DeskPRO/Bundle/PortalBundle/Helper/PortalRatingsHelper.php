<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Rating;
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

    public function rateContentUp(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, false);

        if ($content_rating) {
            $this->em->persist($content_rating);
            $this->em->flush([$content_rating, $content]);
        }

        return $content_rating;
    }

    public function rateContentDown(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, true);

        if ($content_rating) {
            $this->em->persist($content_rating);
            $this->em->flush([$content_rating, $content]);
        }

        return $content_rating;
    }

    /**
     * @param ContentAbstract $content
     * @param Person|null $person
     *
     * @param null $visitorId
     * @return ContentAbstract|false
     * @throws OptimisticLockException
     */
    public function removeContentRating(ContentAbstract $content, Person $person = null, $visitorId = null)
    {
        $contentRating = ($this->findPersonRating($content, $person)) ?? $this->findVisitorRating($content, $visitorId);

        if (null === $contentRating) {
            return false;
        }

        $content->removeRating($contentRating);

        $this->em->remove($contentRating);
        $this->em->persist($content);
        $this->em->flush();

        return $content;
    }

    public function updatePersistedOrCreateNewRating(ContentAbstract $content, $visitor_id, $person, $down = false)
    {
        $contentRating = ($this->findPersonRating($content, $person)) ?? $this->findVisitorRating($content, $visitor_id);

        //Already Upvoted
        if (null !== $contentRating && !$down) {
            return false;
        }

        if ($contentRating) {
            if ($person && null === $contentRating->getPerson()) {
                $contentRating->setPerson($person);
            }

            $this->changeExistingRating($content, $contentRating, $down);

            return $contentRating;
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
