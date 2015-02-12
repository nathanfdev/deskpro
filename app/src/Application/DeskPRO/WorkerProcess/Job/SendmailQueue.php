<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;
use Application\DeskPRO\App;
use Application\EmailBundle\SourceMapper\ExternalPendingQueue;

/**
 * Goes through queued messages
 */
class SendmailQueue extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    public function run()
    {
        $runner = App::getContainer()->get('email.queue_runner');
        $count_problems = $runner->detectProblems();
        $count = 0;

        $source_mapper = App::getContainer()->get('email.source_mapper');

        // If we are using an external pending queue implementation,
        // then this cron job should NOT run the main queue loop
        // because the external queue is responsible for that
        if (!($source_mapper instanceof ExternalPendingQueue)) {
            @ini_set('memory_limit', DP_MAX_MEMSIZE);
            $count = $runner->run();
            @ini_set('memory_limit', DP_SET_MEMSIZE);
        }

        if ($count_problems) {
            $this->logStatus("Detected {$count_problems} probelms in queue. Marked those as error:timeout.");
        }
        if ($count) {
            $this->logStatus("Processed {$count} emails in queue.");
        }
    }
}
