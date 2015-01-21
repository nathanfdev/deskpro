<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Helper;


use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\Entity\Person;
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
        $this->em = $em;
        $this->request_stack = $request_stack;
    }

    public function rateContentUp(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, false);

        $this->em->persist($content_rating);
        $this->em->flush(array($content_rating, $content));

        return $content_rating;
    }

    public function rateContentDown(ContentAbstract $content, $visitor_id, Person $person = null)
    {
        $content_rating = $this->updatePersistedOrCreateNewRating($content, $visitor_id, $person, true);

        $this->em->persist($content_rating);
        $this->em->flush(array($content_rating, $content));

        return $content_rating;
    }

    public function updatePersistedOrCreateNewRating(ContentAbstract $content, $visitor_id, $person, $down = false)
    {
        if ($person && $content_rating = $this->findPersonRating($content, $person)) {
            $this->changeExistingRating($content, $content_rating, $down);
            return $content_rating;
        }

        if (!$person && $content_rating = $this->findVisitorRating($content, $visitor_id)) {
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
     * @param Person $person
     * @return Rating|null
     */
    public function findPersonRating(ContentAbstract $content, Person $person = null)
    {
        if (!$person) {
            return null;
        }

        $res = $this->em->createQuery("
                SELECT r
                FROM DeskPRO:Rating r
                WHERE
                    r.object_type = ?1 AND r.object_id = ?2
                    AND (r.person = ?3)
            ")
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->setParameter(3, $person)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return null;
    }

    /**
     * @param ContentAbstract $content
     * @param $visitor_id
     * @return Rating|null
     */
    public function findVisitorRating(ContentAbstract $content, $visitor_id)
    {
        if (!$visitor_id) {
            return null;
        }

        $res = $this->em->createQuery("
                SELECT r
                FROM DeskPRO:Rating r
                WHERE
                    r.object_type = ?1 AND r.object_id = ?2
                    AND (r.visitor_id = ?3)
            ")
            ->setParameter(1, $content->getContentType())
            ->setParameter(2, $content->getId())
            ->setParameter(3, $visitor_id)
            ->execute();

        if ($res and count($res)) {
            return $res[0];
        }

        return null;
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
}
