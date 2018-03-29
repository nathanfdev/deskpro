<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

/**
 * A job is "checkable" when it's easily tested to see if work needs to be done.
 * Most of these jobs are meant to listen for events in a daemon that is continuously
 * run.
 */
interface CheckableInterface
{
    /**
     * Is run() ready to be called?
     *
     * @return bool
     */
    public function isReady();

    /**
     * How long should the main process sleep before trying to check isReady()
     * again? The time should be returned in microseconds (ie usable by usleep()).
     *
     * @return int
     */
    public function getReadyCheckDelay();
}
