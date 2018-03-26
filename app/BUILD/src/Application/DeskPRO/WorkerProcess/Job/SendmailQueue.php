<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\EmailBundle\SourceMapper\ExternalPendingQueue;

/**
 * Goes through queued messages.
 */
class SendmailQueue extends AbstractJob
{
    const DEFAULT_INTERVAL = 1;

    public function run()
    {
        $runner         = App::getContainer()->get('email.queue_runner');
        $count_problems = $runner->detectProblems();
        $count          = 0;

        $source_mapper = App::getContainer()->get('email.source_mapper');

        // If we are using an external pending queue implementation,
        // then this cron job should NOT run the main queue loop
        // because the external queue is responsible for that
        if (!($source_mapper instanceof ExternalPendingQueue)) {
            @ini_set('memory_limit', DP_MAX_MEMSIZE);
            $count = $runner->run();
            @ini_set('memory_limit', DP_USE_MEMSIZE);
        }

        if ($count_problems) {
            $this->logStatus("Detected {$count_problems} probelms in queue. Marked those as error:timeout.");
        }
        if ($count) {
            $this->logStatus("Processed {$count} emails in queue.");
        }
    }
}
