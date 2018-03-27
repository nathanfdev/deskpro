<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\Cleaner;

/**
 * Cleans hit records.
 */
interface HitCleanerInterface
{
    /**
     * @param \DateTime $last_clean
     */
    public function clean(\DateTime $last_clean);
}
