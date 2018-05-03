<?php

namespace DeskPRO\Bundle\UpdateBundle\Instance;

use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;

class InstanceStatus
{
    /**
     * @var DistroRelease
     */
    private $currentRelease;

    /**
     * @var DistroRelease
     */
    private $latestRelease;

    /**
     * @var int
     */
    private $numBetween;

    /**
     * InstanceState constructor.
     *
     * @param DistroRelease $currentRelease
     * @param DistroRelease $latestRelease
     * @param int           $numBetween
     */
    public function __construct(DistroRelease $currentRelease, DistroRelease $latestRelease, $numBetween)
    {
        $this->currentRelease = $currentRelease;
        $this->latestRelease  = $latestRelease;
        $this->numBetween     = $numBetween;
    }

    /**
     * @return DistroRelease
     */
    public function getCurrentRelease()
    {
        return $this->currentRelease;
    }

    /**
     * @return DistroRelease
     */
    public function getLatestRelease()
    {
        return $this->latestRelease;
    }

    /**
     * @return int
     */
    public function getNumBetween()
    {
        return $this->numBetween;
    }

    /**
     * @return bool
     */
    public function isOutdated()
    {
        return $this->currentRelease->getDate() < $this->latestRelease->getDate();
    }

    /**
     * @return int
     */
    public function getDaysOld()
    {
        if (!$this->isOutdated()) {
            return 0;
        }

        $interval = $this->latestRelease->getDate()->diff($this->currentRelease->getDate());

        return max(1, (int) $interval->format('%a'));
    }
}
