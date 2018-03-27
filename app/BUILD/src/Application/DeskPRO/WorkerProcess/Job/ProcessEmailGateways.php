<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DpSys\LowError\SystemErrorHandler;

/**
 * Goes through each gateway and processes email.
 */
class ProcessEmailGateways extends AbstractJob
{
    const DEFAULT_INTERVAL = 1;

    public function run()
    {
        // Using adv_email_collect (daemon)
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        if ($DP_ENV->getConfig('adv_email_collect')) {
            return;
        }

        @ini_set('memory_limit', DP_MAX_MEMSIZE);

        //------------------------------
        // Mark error sources
        //------------------------------

        // If a source has been in the 'processing' state for more than 15 mintues,
        // then it means it's probably an error
        $d = date('Y-m-d H:i:s', time() - 900);

        // Retry those that havent been retried
        $num = App::getDb()->executeUpdate("
            UPDATE email_sources
            SET status = 'retry', error_code = NULL
            WHERE status = 'processing' AND date_created < ? AND exec_count <= 2
        ", [$d]);

        if ($num) {
            $this->getLogger()->logNotice("$num email sources(s) marked as timeout and will be retried");
        }

        $num = App::getDb()->executeUpdate("
            UPDATE email_sources
            SET status = 'error', error_code = 'timeout'
            WHERE status = 'processing' AND date_created < ? AND exec_count >= 2
        ", [$d]);

        if ($num) {
            $e = new \Exception("$num email source(s) marked as timeout and will not be retried because they are over the retry threshold");
            SystemErrorHandler::logException($e);
            $this->getLogger()->log("$num sources marked as timeout and will not be retried", 'ERR');
        }

        //------------------------------
        // Run the gateways
        //------------------------------

        $logger = $this->getLogger();

        $runner = new \Application\DeskPRO\EmailGateway\Runner();
        $runner->setLogger($logger);

        $runner->setPhpTimeLimit(900);
        $runner->setSoftTimeLimit(480);
        $runner->setMessageLimit(40);

        if ($this->options->get('run_source_id')) {
            $sid    = $this->options->get('run_source_id');
            $source = App::getOrm()->find('DeskPRO:EmailSource', $sid);
            if (!$source) {
                $this->getLogger()->log("No source with ID $sid", 'NOTICE');

                return;
            }

            $runner->executeSource($source);
        } elseif ($this->options->get('run_account_id')) {
            $gid = $this->options->get('run_account_id');
            $this->getLogger()->log("Running specific account: $gid", 'DEBUG');

            $account = App::getOrm()->find('DeskPRO:EmailAccount', $this->options->get('run_account_id'));
            if (!$account) {
                $this->getLogger()->log("No account with ID $gid", 'NOTICE');

                return;
            }

            $runner->setAccounts([$account]);
            $runner->execute(300);
        } else {
            $runner->loadAccountsFromDb(false);
            $runner->execute(300);
        }

        // The PHP time limit would've been set above while processing messages,
        // reset it to disabled so other cron tasks can finish in this same execution
        @set_time_limit(0);
        @ini_set('memory_limit', DP_USE_MEMSIZE);
    }
}
