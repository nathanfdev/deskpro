<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\EntityRepository\Rating as RatingRepo;

class RatingsDataService extends AbstractDataService
{
    /**
     * @param int|null|Rating $rating
     *
     * @return Rating|null
     */
    public function getRating($rating)
    {
        $ratings_repo = $this->getRatingRepo();

        return $this->generateAndCache(
            [
                'getRating',
                $rating,
            ],
            function () use ($ratings_repo, $rating) {
                if (!$rating) { // we need some input
                    return;
                }

                if ($rating instanceof Rating) { // already have what you seek
                    return $rating;
                }

                return $ratings_repo->find($rating);
            }
        );
    }

    /**
     * @return RatingRepo
     */
    public function getRatingRepo()
    {
        return $this->em->getRepository('DeskPRO:Rating');
    }
}
