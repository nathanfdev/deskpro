<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class JobQueueSupervisor extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    public function run()
    {
        $supervisor = App::getContainer()->getJobSupervisor();
        $supervisor->run();
    }
}
