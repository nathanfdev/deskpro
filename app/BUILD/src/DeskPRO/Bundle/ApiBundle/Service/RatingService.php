<?php

namespace DeskPRO\Bundle\ApiBundle\Service;

use Application\DeskPRO\Entity\Rating;
use DeskPRO\Bundle\AppBundle\Model\RatingModel;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Helper\PortalRatingsHelper;

/**
 * Class RatingService
 * @package DeskPRO\Bundle\ApiBundle\Service
 */
class RatingService
{
    /**
     * @var PortalRatingsHelper
     */
    private PortalRatingsHelper $ratingHelper;
    /**
     * @var BrandStack
     */
    private BrandStack $bandStack;

    /**
     * RatingService constructor.
     * @param PortalRatingsHelper $ratingHelper
     * @param BrandStack $brandStack
     */
    public function __construct(PortalRatingsHelper $ratingHelper, BrandStack $brandStack)
    {
        $this->ratingHelper = $ratingHelper;
        $this->bandStack    = $brandStack;
    }

    /**
     * @param $content
     * @param $upvote
     * @param $person
     * @param null $visitorId
     * @return Rating|null
     */
    public function rateContent($content, $upvote, $person, $visitorId = null)
    {
        if ($upvote) {
            $rate = $this->ratingHelper->rateContentUp($content, $visitorId, $person);
        } else {
            $rate = $this->ratingHelper->rateContentDown($content, $visitorId, $person);
        }

        return $rate;
    }

    /**
     * @param RatingModel $ratingModel
     * @return array
     */
    public function ratingCount(RatingModel $ratingModel): array
    {
        $show_rating_counts = false;
        $rating_counts      = ['positive' => 0, 'total' => 0];
        if ($this->bandStack->getActive()->getSetting('user.show_ratings')) {
            $rating_counts = $this->ratingHelper->ratingCounts($ratingModel);
            if ($rating_counts['total'] >= $this->bandStack->getActive()->getSetting('user.show_ratings_min_votes')) {
                $show_rating_counts = true;
            }
        }

        return [$show_rating_counts, $rating_counts];
    }
}
