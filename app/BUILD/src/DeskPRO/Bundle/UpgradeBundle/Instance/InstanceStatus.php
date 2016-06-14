<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\UpgradeBundle\Instance;

use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroRelease;

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

        return max(1, (int) $interval->format('%d'));
    }
}
