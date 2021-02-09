<?php

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\ContentAbstract;

/**
 * Class RatingModel.
 */
class RatingModel
{
    private $contentType;

    private $contentId;

    private $num_ratings;

    private $total_rating;

    private $object;

    public function getContentId()
    {
        return $this->contentId;
    }

    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getUpVotes()
    {
        $stats = $this->getVoteStats();

        return $stats['up'];
    }

    /**
     * Vote stats object, like {"up": 1, "down": 1}.
     */
    public function getVoteStats()
    {
        $x = $this->getNumRatings() - abs($this->getTotalRating());

        if ($x % 2 === 1) {
            ++$x; // never happens with correct data, this just error corrects
        }

        if ($this->getTotalRating() >= 0) {
            $up   = ($x / 2) + $this->getTotalRating();
            $down = ($x / 2);
        } else {
            $up   = ($x / 2);
            $down = ($x / 2) + abs($this->getTotalRating());
        }

        return ['up' => $up, 'down' => $down];
    }

    /**
     * @return int
     */
    public function getNumRatings()
    {
        return $this->num_ratings;
    }

    public function setNumRatings($num_ratings)
    {
        $this->num_ratings = $num_ratings;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalRating()
    {
        return $this->total_rating;
    }

    public function setTotalRating($totalRating)
    {
        $this->total_rating = $totalRating;

        return $this;
    }

    public function getDownVotes()
    {
        $stats = $this->getVoteStats();

        return $stats['down'];
    }

    public function markRatingChangedPositivly()
    {
        $this->getObject()->setTotalRating($this->getTotalRating() + 1);
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function markRatingChangedNegatively()
    {
        $this->getObject()->setTotalRating($this->getTotalRating() - 1);
    }

    public function getRatingPercent()
    {
        if (!$this->getNumRatings()) {
            return 0;
        }

        return min(100, ceil(($this->getTotalRating() / $this->getNumRatings()) * 100));
    }

    public function addRating($rating)
    {
        if ($this->getObject() instanceof ContentAbstract) {
            $this->getObject()->setNumRatings($this->getNumRatings() + 1);
        }
        $this->getObject()->setTotalRating($this->getTotalRating() + $rating->rating);
    }

    public function removeRating($rating)
    {
        if ($this->getObject() instanceof ContentAbstract) {
            $this->getObject()->setNumRatings($this->getNumRatings() - 1);
        }
        $this->getObject()->setTotalRating($this->getTotalRating() - $rating->rating);
    }

}
