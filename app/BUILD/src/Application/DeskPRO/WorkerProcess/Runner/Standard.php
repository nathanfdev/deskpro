<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Runner;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;
use Orb\Log\Filter\SimpleLineFormatter;

/**
 * A standard runner executes all jobs in sequence one at a time.
 */
class Standard extends AbstractRunner
{
    /** @var bool */
    protected $is_verbose = false;
    public function setVerbose()
    {
        $this->is_verbose = true;
    }

    public function _initLogger(Logger $logger, Entity\WorkerJob $worker_job)
    {
        if ($this->is_verbose) {
            if (isset($GLOBALS['DP_OUTPUT'])) {
                $out_writer = new \Orb\Log\Writer\ConsoleOutputWriter($GLOBALS['DP_OUTPUT']);
            } else {
                $out_writer = new \Orb\Log\Writer\Stream('php://stdout');
            }
            $out_writer->getFilterChain()->addFilter(new SimpleLineFormatter(), true);
            $logger->addWriter($out_writer);
        }

        if (App::getConfig('debug.write_cron_logfile')) {
            $out_writer = new \Orb\Log\Writer\Stream(dp_get_log_dir().'/cron.log');
            $out_writer->getFilterChain()->addFilter(new SimpleLineFormatter(), true);
            $logger->addWriter($out_writer);
        }
    }
}
