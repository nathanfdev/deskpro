<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

/**
 * Updates agents online through dispatching event for action alerts.
 */
class ProcessPersistedEvents extends AbstractJob
{
    const DEFAULT_INTERVAL = 60; // 1 minute

    public function run()
    {
        // No-op as this process is redundant
    }
}
