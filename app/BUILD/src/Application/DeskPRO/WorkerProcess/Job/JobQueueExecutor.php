<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\JobQueue\JobWorker;

class JobQueueExecutor extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    public function run()
    {
        // the old job "WorkerProcess" system wasn't designed with DI in mind, using the globals
        $connection = App::getDb();
        $router     = App::$container->getSystemService('job_router');
        $queue      = App::$container->getSystemService('job_queue');
        $worker     = new JobWorker($connection, $router, $queue);

        $worker->work(25);
    }
}
