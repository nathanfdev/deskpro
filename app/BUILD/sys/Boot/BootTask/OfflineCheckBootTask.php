<?php

namespace DpSys\Boot\BootTask;

use Symfony\Component\HttpFoundation\Request;

/**
 * This checks to see if the helpdesk is offline and if we need to quit early.
 */
class OfflineCheckBootTask implements BootTaskInterface
{
    /**
     * @var \DpRun\DpEnv
     */
    private $env;

    public function run(\DpRun\DpEnv $env, array $resources)
    {
        $this->env = $env;

        if (!$this->isHelpdeskOffline()) {
            return;
        }

        $isCli        = php_sapi_name() === 'cli';
        $isCron       = false;
        $isCliVerbose = false;

        if ($isCli) {
            $argv = !empty($resources['argv']) ? $resources['argv'] : $_SERVER['argv'];
            if (in_array('dp:worker-job', $argv)) {
                $isCron = true;
            } else {
                // A manually run CLI command (i.e. not cron) doesn't get disabled
                return;
            }

            if (in_array('-v', $argv) || in_array('-vv', $argv) || in_array('-vvv', $argv) || in_array('--verbose', $argv)) {
                $isCliVerbose = true;
            }
        }

        if ($isCron) {
            $mode = 'cron';
        } elseif ($isCli) {
            $mode = 'cli';
        } elseif (!empty($resources['request'])) {
            /** @var Request $request */
            $request = $resources['request'];

            if (in_array('application/json', $request->getAcceptableContentTypes())) {
                $mode = 'json';
            } elseif (in_array('text/plain', $request->getAcceptableContentTypes())) {
                $mode = 'text';
            } else {
                $mode = 'html';
            }
        } else {
            $mode = 'text';
        }

        $message    = $env->getDatManager()->readTxtFile('helpdesk-offline-message', null) ?: 'The helpdesk is currently offline for routine maintenance.';
        $messageTxt = trim(strip_tags($message));

        switch ($mode) {
            case 'cli':
            case 'cron':
                if (!$isCron || $isCliVerbose) {
                    echo $messageTxt;
                }
                break;

            case 'text':
                header('HTTP/1.0 423 Locked');
                header('Content-Type: text/plain');
                echo $messageTxt;
                break;
            case 'json':
                header('HTTP/1.0 423 Locked');
                header('Content-Type: application/json');
                echo json_encode([
                    'error'   => 'helpdesk_offline',
                    'message' => $messageTxt,
                ]);
                break;
            case 'html':
                header('HTTP/1.0 423 Locked');
                header('Content-Type: text/html');
                echo '<html><head><title></title></head><body>';
                echo $message;
                echo '</body></html>';
                break;
        }

        // exit right now
        exit;
    }

    /**
     * @return bool
     */
    private function isHelpdeskOffline()
    {
        if (isset($GLOBALS['DP_HELPDESK_DISABLED']) && $GLOBALS['DP_HELPDESK_DISABLED']) {
            return true;
        }

        // Offline file is inserted on cmdline upgrade,
        // we want to disable all access
        if ($this->env->getDatManager()->hasTrigger('helpdesk-offline')) {
            return true;
        }

        return false;
    }
}
