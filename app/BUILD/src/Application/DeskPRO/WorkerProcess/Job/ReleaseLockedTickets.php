<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

/**
 * Releases old locks and locks that are from agents who have gone offline.
 */
class ReleaseLockedTickets extends AbstractJob
{
    const DEFAULT_INTERVAL = 180;

    public function run()
    {
        //------------------------------
        // agent offline check
        //------------------------------

        $offset = 60 * 2; // 2 minutes offline offset
        $n      = App::getOrm()->getRepository('DeskPRO:Ticket')->unlockOfflineAgentsTickets($offset);

        if ($n) {
            $this->logStatus("Released $n stale locks");
        }

        //------------------------------
        // ticket locks
        //------------------------------

        $n = App::getOrm()->getRepository('DeskPRO:Ticket')->unlockTicketsByTime(App::getSetting('core_tickets.lock_lifetime'));

        if ($n) {
            $this->logStatus("Cleaned up $n ticket locks");
        }
    }
}
