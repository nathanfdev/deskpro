<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;

class Heartbeat extends AbstractJob
{
    const DEFAULT_INTERVAL = 86400;

    public function run()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        if (!isset($GLOBALS['DP_CRON_IGNORE_INTERVAL']) || !$GLOBALS['DP_CRON_IGNORE_INTERVAL']) {
            $last = App::getSetting('core.last_heartbeat');
            if ($last && $last > (time() - 85000)) {
                // already sent it today
                return;
            }
        }

        App::$container->getSettingsHandler()->setSetting('core.last_heartbeat', time());

        $ret_data = \Application\DeskPRO\Service\ErrorReporter::sendHeartbeat();

        if ($ret_data && ($ret_data = @json_decode($ret_data, true))) {
            if (!empty($ret_data['replace_license_code'])) {
                App::getDb()->replace('settings', [
                    'name'  => 'core.license',
                    'value' => $ret_data['replace_license_code'],
                ]);
                $this->logStatus('Updated license code');
            }
        }
    }
}
