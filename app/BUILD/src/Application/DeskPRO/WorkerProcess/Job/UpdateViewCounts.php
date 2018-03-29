<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

/**
 * Updates viewcounts on articles and handles cleanup of hittracks.
 */
class UpdateViewCounts extends AbstractJob
{
    const DEFAULT_INTERVAL = 600; // 10 minutes

    public function run()
    {
        $counter = $this->getContainer()->get('hitrecord.viewcounts.counter');
        $views   = $counter->getViews(new \DateTime('-10 minutes'));

        $updater = $this->getContainer()->get('hitrecord.viewcounts.updater');
        $updater->updateViews($views);

        $cleaner = $this->getContainer()->get('hitrecord.cleaner');
        $cleaner->clean(new \DateTime('-10 minutes'));
    }
}
