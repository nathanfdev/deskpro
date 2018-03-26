<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\ViewCount;

/**
 * A view counter is called on a cron job (UpdateViewCounts) every 10 minutes.
 * The purpose is to go through the hit log and count views on DeskPRO content: articles, news, downloads, feedback.
 *
 * How it does this is up to the implementation.
 */
interface ViewCounterInterface
{
    /**
     * @param \DateTime $last_proc_date
     *
     * @return Views
     */
    public function getViews(\DateTime $last_proc_date);
}
