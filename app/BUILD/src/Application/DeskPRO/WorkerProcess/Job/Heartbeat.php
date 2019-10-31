<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

class Heartbeat extends AbstractJob
{
    const DEFAULT_INTERVAL = 1800;

    public function run()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        $ret_data = \Application\DeskPRO\Service\ErrorReporter::sendHeartbeat();

        if ($ret_data && ($ret_data = @json_decode($ret_data, true))) {
            if (!empty($ret_data['replace_license_code'])) {
                $this->logStatus('Updated license code');
            }
        }
    }
}
