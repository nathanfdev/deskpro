<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class CleanupWeekly extends AbstractJob
{
    const DEFAULT_INTERVAL = 604800;

    public function run()
    {
        $this->doRun();
        App::getDb()->setIsolationDefault();
    }

    private function doRun()
    {
        $date = date('Y-m-d H:i:s', strtotime('-1 year'));

        $num = App::getDb()->executeUpdate('
            DELETE FROM login_log
            WHERE date_created < ?
        ', [$date]);

        if ($num) {
            $this->logStatus("Cleaned up $num old login logs");
        }
    }
}
