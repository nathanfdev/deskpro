<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Log\Logger;
use DeskPRO\Bundle\UpdateBundle\Logger\LogKeyEvent;
use Orb\Util\Env;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WorkerJobCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /** @var bool */
    protected $set_verbose = false;
    /** @var bool */
    protected $ignore_interval = false;
    /** @var OutputInterface */
    protected $output;
    /** @var string */
    protected $cron_id;

    protected function configure()
    {
        $this->setName('dp:worker-job')
            ->addOption('job', 'j', InputOption::VALUE_REQUIRED, 'Run only specific job')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Run only a specific group of jobs')
            ->addOption('ignore-interval', 'f', InputOption::VALUE_NONE, 'Always run job(s) even if the job interval has not ellapsed since last run')
            ->addOption('no-croncheck', null, InputOption::VALUE_NONE, 'Skip the cron checker (used when cron is run with another process manager)')
            ->addOption('options', 'o', InputOption::VALUE_REQUIRED, 'Specify a JSON-encoded array of options to pass to worker jobs')
            ->addOption('no-auto-updater', null, InputOption::VALUE_NONE, 'Do NOT start any auto-update process')
            ->addOption('auto-updater', null, InputOption::VALUE_NONE, 'Start the auto-update process if it is scheduled. This will block/wait if another cron instance is still running and start the update after it finishes.')
            ->addOption('info', null, InputOption::VALUE_NONE, 'Don\'t execute anything, just list info about scheduled tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* \DpRun\DpEnv */
        global $DP_ENV;
        $DP_ENV->getDatManager()->enableTrigger('cron_has_run');

        $time_cron_start = microtime(true);

        @ini_set('track_errors', true);

        $GLOBALS['DP_PREF_MAX_EXEC_TIME'] = 1800;
        @set_time_limit($GLOBALS['DP_PREF_MAX_EXEC_TIME']);

        $isVerbose = $output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE;
        $appEnv    = $this->getContainer()->get('deskpro.app_env');

        // see if we need to do an ES index
        if (!defined('DPC_IS_CLOUD')) {
            @set_time_limit(0);
            $index_reset = \Application\DeskPRO\App::getSetting('elastica.requires_reset');
            if ($index_reset) {
                try {
                    $id = mt_rand(10000, 99999);
                    \Application\DeskPRO\App::getDb()->insertIgnore('settings', ['name' => 'elastica.requires_reset_started', 'value' => $id]);

                    $cmd = $appEnv->getConsolePhpCommand('dp:elastica:populate --auto-reset '.$id);

                    if ($isVerbose && defined('DP_START_TIME')) {
                        $output->writeln('Starting ElasticSearch indexing: '.$cmd);
                    }

                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        // this is needed as we need a fake window to hide the process
                        $cmd = str_replace('php-win.exe', 'php.exe', $cmd);

                        if (class_exists('\COM', false)) {
                            $shell = new \COM('WScript.Shell');
                            $shell->Run($cmd, 0, false);
                        } else {
                            pclose(popen("start \"dpindexer\" /MIN $cmd", 'r'));
                        }
                    } else {
                        exec("nohup $cmd > /dev/null 2> /dev/null &");
                    }
                } catch (\Exception $e) {
                }
            }
        }

        if ($isVerbose && defined('DP_START_TIME')) {
            $output->writeln(sprintf('[%s] Time to enter execute: %.4f', date('Y-m-d H:i:s'), $time_cron_start - DP_START_TIME));
        }

        if ($input->getOption('info')) {
            $jobs = App::getOrm()->createQuery('
                SELECT j
                FROM DeskPRO:WorkerJob j
                ORDER BY j.last_run_date ASC
            ')->execute();

            $last_run = App::getSetting('core.last_cron_run');
            if (!$last_run) {
                $last_run = 0;
            }

            $time_since_run = time() - $last_run;
            $is_problem     = false;
            if ($time_since_run > 301) {
                $is_problem = true;
            }

            if (!$last_run) {
                $output->writeln('Last run time: NEVER');
            } else {
                $output->writeln(sprintf('Last run time: %s (%s)', date('Y-m-d H:i:s', $last_run), \Orb\Util\Dates::secsToReadable(time() - $last_run, 5)));

                if ($is_problem) {
                    $output->writeln('');
                    $output->writeln('<info>Tasks have not completed successfully in a while which could indicate a problem. Try running this command with --verbose -f to force all jobs to run with output.</info>');
                }
            }

            $output->writeln('');

            $format = '%-30s  %-4s  %-16s  %-16s';
            $output->writeln(sprintf($format, 'Job', 'Int.', 'Last Run', 'Next Run'));
            $output->writeln(sprintf($format, str_repeat('=', 30), str_repeat('=', 4), str_repeat('=', 16), str_repeat('=', 16)));

            foreach ($jobs as $j) {
                $output->writeln(sprintf(
                    '%-30s  %-4s  %-16s  %-16s',
                    $j->id,
                    $j->getIntervalReadable(),
                    $j->last_run_date ? \Orb\Util\Dates::dateToAgo($j->last_run_date, 3, 'short') : 'Never',
                    $j->next_run_date ? \Orb\Util\Dates::dateToAgo($j->next_run_date, 3, 'short') : 'NA'
                ));
            }

            $output->writeln('');

            return 0;
        }

        $skipUpdater = $input->getOption('no-auto-updater');
        $onlyUpdater = $input->getOption('auto-updater');

        $cron_id = 'dp-cron';
        if ($input->getOption('job')) {
            $cron_id .= '-'.$input->getOption('job');
        } elseif ($input->getOption('group')) {
            $cron_id .= '-g-'.$input->getOption('group');
        }

        //------------------------------
        // Clean up installer error detection
        //------------------------------

        if (file_exists(dp_get_log_dir().'/cron-boot-errors.log')) {
            @unlink(dp_get_log_dir().'/cron-boot-errors.log');
        }

        App::getDb()->delete('install_data', ['build' => 1, 'name' => 'cron_run_errors']);

        //------------------------------
        // Auto-upgrader
        //------------------------------

        if (!$skipUpdater) {
            $updaterSettings = $this->getContainer()->get('updater_settings_resolver')->getUpdaterSettings();
            $updaterStatus   = $this->getContainer()->get('updater_settings_resolver')->getUpdaterStatus();

            $check = App::getDb()->fetchColumn('SELECT value FROM settings WHERE name LIKE ?', ['core.croncheck.updater']);
            if ($check && $check > (time() - 3600)) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln(sprintf('[%] core.croncheck.updater already running', date('Y-m-d H:i:s')));
                }

                return 0;
            }

            App::getDb()->replace('settings', ['name' => 'core.croncheck.updater', 'value' => time()]);
            $db = App::getDb();

            register_shutdown_function(function () use ($db) {
                $db->delete('settings', ['name' => 'core.croncheck.updater']);
            });

            if ($updaterStatus->getNextCheck() && $updaterStatus->getNextCheck() < (new \DateTime())) {
                do {
                    $check = App::getDb()->fetchColumn('SELECT value FROM settings WHERE name LIKE ? AND name != ?', ['core.croncheck.%', 'core.croncheck.updater']);
                    if ($check) {
                        if ($check < time() - 3600) {
                            return 0;
                        }
                        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                            $output->writeln('Waiting ...');
                        }
                        sleep(1);
                    }
                } while ($check);

                if ($updaterStatus->isNextManual()) {
                    $cmd = $appEnv->getConsolePhpCommand('dp:update --no-interaction');
                } else {
                    $cmd = $appEnv->getConsolePhpCommand('dp:update --no-interaction --only-auto');
                }
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln("Running upgrade: $cmd");
                    $cb = function ($t, $l) use ($output) {
                        $output->write($l);
                    };
                } else {
                    $cb = null;
                }

                $container = $this->getContainer();

                // we need to fetch the updater logger and session
                // to handle error cases where the command fails
                $getLogger = function () use ($container) {
                    /* @var $DP_ENV \DpRun\DpEnv */
                    global $DP_ENV;

                    $sessionId = $DP_ENV->getDatManager()->readTxtFile('last_updater_session_id', null);
                    if ($sessionId) {
                        $smf = $this->getContainer()->get('dp.updater.session_manager_factory');
                        $smf->enableSessionId($sessionId);
                    }

                    $logger = $this->getContainer()->get('monolog.logger.updater.general');

                    return $logger;
                };

                try {
                    $process = new Process($cmd);
                    $process->setTimeout(36000);
                    $process->run($cb);

                    if (!$process->isSuccessful()) {
                        $e = new \RuntimeException('Updater exited with a non-success status: '.$process->getExitCode().' ('.$process->getExitCodeText().')');
                        $output->writeln('<error>Updater stopped unexpectedly: '.$e->getMessage().'</error>');
                        $getLogger()->error(
                            'Updater from cron: finished unexpectedly',
                            ['keyEvent' => LogKeyEvent::createForException('AutoUpgrade.error', $e)]
                        );
                    }
                } catch (\Exception $e) {
                    $output->writeln('<error>Updater stopped unexpectedly: '.$e->getMessage().'</error>');
                    $getLogger()->error(
                        'Updater from cron: finished unexpectedly',
                        ['keyEvent' => LogKeyEvent::createForException('AutoUpgrade.error', $e)]
                    );
                }

                return 0;
            }

            $db->delete('settings', ['name' => 'core.croncheck.updater']);

            // If we got here, there is nothing to do, so return
            if ($onlyUpdater) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln('No update is scheduled');
                }

                return 0;
            }
        }

        //------------------------------
        // CLI phpinfo
        //------------------------------

        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        @file_put_contents(
            $appEnv->getUserCacheDir().'/cli-phpinfo.html',
            $phpinfo
        );

        @file_put_contents(
            $appEnv->getUserCacheDir().'/cli-phpconfig.json',
            json_encode([
                'version'      => phpversion(),
                'memory_limit' => Env::getMemoryLimit(),
                'error_log'    => ini_get('error_log'),
            ], \JSON_PRETTY_PRINT)
        );

        //------------------------------
        // Import jobs
        //------------------------------

        $em              = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $importerRunning = App::getDb()->fetchColumn('SELECT value FROM settings WHERE name LIKE ?', ['core.croncheck.importer']);
        $importerJob     = $this->getContainer()->get('dp.importer.data_service.job')->getWaitingJob();
        if (!$importerRunning && $importerJob) {
            App::getDb()->replace('settings', [
                'name'  => 'core.croncheck.importer',
                'value' => 1,
            ]);

            // run the job
            $failed  = false;
            $cmd     = $appEnv->getConsolePhpCommand("dp:import -j {$importerJob->getId()}");
            $process = new Process($cmd);
            $process->setTimeout(null);

            if ($isVerbose) {
                $process->run(function ($type, $dat) use ($output) {
                    if ($type === Process::OUT) {
                        $output->writeln($dat);
                    } else {
                        $output->writeln('ERR: '.$dat);
                    }
                });
            } else {
                $process->run();
            }

            if ($process->isSuccessful()) {
                $cmd     = $appEnv->getConsolePhpCommand("dp:import:apply -j {$importerJob->getId()}");
                $process = new Process($cmd);
                $process->setTimeout(null);

                if ($isVerbose) {
                    $process->run(function ($type, $dat) use ($output) {
                        if ($type === Process::OUT) {
                            $output->writeln($dat);
                        } else {
                            $output->writeln('ERR: '.$dat);
                        }
                    });
                } else {
                    $process->run();
                }

                if (!$process->isSuccessful()) {
                    $failed = true;
                }
            } else {
                $failed = true;
            }

            if ($failed) {
                $importerJob->setStatus(Job::STATUS_ERROR);
                $em->persist($importerJob);
                $em->flush();
            }

            App::getDb()->delete('settings', ['name' => 'core.croncheck.importer']);

            return 0;
        }

        //------------------------------
        // Run
        //------------------------------

        $time_start = microtime(true);
        if (!defined('DP_DISABLE_DBCRONLOG')) {
            App::getDb()->insert('log_items', [
                'log_name'      => 'worker_job.cron_runner',
                'session_name'  => 'cron_runner.'.$time_start,
                'flag'          => 'cron_start',
                'priority'      => 6,
                'priority_name' => 'INFO',
                'message'       => 'Cron runner started',
                'date_created'  => date('Y-m-d H:i:s'),
            ]);
        }
        App::getDb()->replace('settings', ['name' => 'core.last_cron_start', 'value' => time()]);

        $GLOBALS['DP_CRON_ID'] = $cron_id;

        if (!$input->getOption('ignore-interval') && !$input->getOption('no-croncheck')) {
            $check = App::getDb()->fetchColumn('SELECT value FROM settings WHERE name = ?', ['core.croncheck.'.$cron_id]);
            if ($check) {
                $date     = (int) $check;
                $date_cut = time() - 900;
                $diff     = \Orb\Util\Dates::secsToReadable(time() - $date, 5);

                if ($date_cut < $date) {
                    if ($input->getOption('verbose')) {
                        $output->writeln(sprintf("[%s] $cron_id is still active. Running for {$diff} (since ".date('Y-m-d H:i:s', $date).')', date('Y-m-d H:i:s')));
                    }
                    App::getDb()->insert('log_items', [
                        'log_name'      => 'worker_job.cron_runner',
                        'session_name'  => 'cron_runner.'.$time_start,
                        'flag'          => 'cron_abort',
                        'priority'      => 6,
                        'priority_name' => 'INFO',
                        'message'       => 'Cron runner aborted (still running)',
                        'date_created'  => date('Y-m-d H:i:s'),
                    ]);

                    return 0;
                } else {
                    App::getDb()->insert('log_items', [
                        'log_name'      => 'worker_job.cron_runner',
                        'session_name'  => 'cron_runner.'.$time_start,
                        'flag'          => 'cron_resume',
                        'priority'      => 3,
                        'priority_name' => 'ERR',
                        'message'       => "WARNING: Cron ($cron_id) has been active for {$diff}. Assuming crashed process, resuming.",
                        'date_created'  => date('Y-m-d H:i:s'),
                    ]);

                    $title = "WARNING: Cron ($cron_id) has been active for {$diff}. Assuming crashed process, resuming.";

                    $text = "Cron ($cron_id) has been marked as active for {$diff} (since ".date('Y-m-d H:i:s', $date).").\n\n"
                            ."This is most likely caused by a fatal error that prevented the runner from resetting the timer.\n\n"
                            .'Cron will now resume, but this is a problem you should investigate. Refer to the error log files and contact support@deskpro.com.'
                            ."\n\n"
                            ."More information about this error can be found here: https://support.deskpro.com/kb/articles/170\n";

                    $output->writeln($title);
                    $output->writeln($text);

                    $e                           = new Exception\CronRunningException($title);
                    $e_info                      = \DpSys\LowError\SystemErrorHandler::getExceptionInfo($e);
                    $e_info['email']             = true;
                    $e_info['email_subject']     = $title;
                    $e_info['email_body']        = $text;
                    $e_info['email_throttle_id'] = 'email_error_cron_timeout';
                    $e_info['attach_logs']       = true;
                    \DpSys\LowError\SystemErrorHandler::logErrorInfo($e_info);
                }
            }
        }

        App::getDb()->replace('settings', [
            'name'  => 'core.croncheck.'.$cron_id,
            'value' => time(),
        ]);

        \DpShutdown::add(function () {
            // Already done (clean shutdown)
            if (!isset($GLOBALS['DP_CRON_ID'])) {
                return;
            }

            try {
                $last_error = null;
                if (isset($GLOBALS['DP_LAST_ERROR'])) {
                    $last_error = $GLOBALS['DP_LAST_ERROR'];
                }

                if ($last_error) {
                    $e = new \Exception('Cron did not shut down cleanly. Last error: '.implode("\n", $last_error));
                    \DpSys\LowError\SystemErrorHandler::logException($e, false);
                } else {
                    $e = new \Exception('Cron did not shut down cleanly');
                    \DpSys\LowError\SystemErrorHandler::logException($e, false);
                }

                App::getDb()->delete('settings', ['name' => 'core.croncheck.'.$GLOBALS['DP_CRON_ID']]);
            } catch (\Exception $e) {
                \DpSys\LowError\SystemErrorHandler::logException($e, false);
            }
        });

        $step = (int) App::getSetting('core.setup_initial');

        // Only run crom if we've passed initial setup
        // This command will just execute nothing and set the last run time
        // so the system knows its been set up
        if ($step) {
            $ret = $this->doExecute($input, $output);
        } else {
            $ret = 0;
        }

        App::getDb()->delete('settings', ['name' => 'core.croncheck.'.$cron_id]);
        App::getDb()->replace('settings', ['name' => 'core.last_cron_run', 'value' => time()]);

        $done_time = microtime(true);
        if (!defined('DP_DISABLE_DBCRONLOG')) {
            App::getDb()->insert('log_items', [
                'log_name'      => 'worker_job.cron_runner',
                'session_name'  => 'cron_runner.'.$time_start,
                'flag'          => 'cron_end',
                'priority'      => 6,
                'priority_name' => 'INFO',
                'message'       => sprintf('Cron runner done. Took %.4f seconds.', $done_time - $time_start),
                'date_created'  => date('Y-m-d H:i:s'),
            ]);
        }

        unset($GLOBALS['DP_CRON_ID']);

        if ($isVerbose) {
            $output->writeln(sprintf('[%s] Time until execute end: %.4f', date('Y-m-d H:i:s'), microtime(true) - $time_cron_start));
        }

        return $ret;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $options = null;
        if ($input->getOption('options')) {
            $options = json_decode($input->getOption('options'), true);
            if (!is_array($options)) {
                $output->writeln('<error>The options array is malformed</error>');

                return 1;
            }
        }
        if (!$options) {
            $options = [];
        }

        $verbose = $input->getOption('verbose');

        if (App::getSetting('core.helpdesk_disabled')) {
            if ($verbose) {
                $output->writeln('<info>Helpdesk is currently disabled.</info>');
            }

            return 0;
        }

        $ignore_interval = false;
        if ($input->getOption('ignore-interval')) {
            $ignore_interval                    = true;
            $GLOBALS['DP_CRON_IGNORE_INTERVAL'] = true;
        }

        $runner = new \Application\DeskPRO\WorkerProcess\Runner\Standard();

        $runner->setJobOptions($options);

        $runner->setPostJobCallback(function ($runner, $worker_job, $logger) {
            $t = microtime(true) - DP_START_TIME;
            if ($t > 600) {
                $runner->haltJobLoop();
                $logger->log(sprintf("haltJobLoop after {$worker_job['id']} :: Time running: %.3fs", $t), Logger::WARN, ['flag' => 'halt_job_loop']);
            }

            // Reset the cron timer so we dont try and restart while we still run
            if (isset($GLOBALS['DP_CRON_ID']) && $GLOBALS['DP_CRON_ID']) {
                App::getDb()->replace('settings', [
                    'name'  => 'core.croncheck.'.$GLOBALS['DP_CRON_ID'],
                    'value' => time(),
                ]);
            }
        });

        if ($verbose) {
            $GLOBALS['DP_OUTPUT'] = $output;
            $runner->setVerbose();
        }

        // A specific job
        if ($input->getOption('job')) {
            $job = App::getEntityRepository('DeskPRO:WorkerJob')->findOneById($input->getOption('job'));
            if (!$job) {
                $output->writeln('<error>No such job exists</error>');

                return 1;
            }

            if (!$ignore_interval and !$job->isReady()) {
                if ($verbose) {
                    $output->writeln('Job does not need to run');
                }

                return 0;
            }

            $runner->runJobs([$job]);

        // A group of jobs
        } elseif ($input->getOption('group')) {
            $group_jobs = App::getOrm()->createQuery('
                SELECT j
                FROM DeskPRO:WorkerJob j
                WHERE j.worker_group = ?1
            ')->setParameter(1, $input->getOption('group'))->execute();

            if (!count($group_jobs)) {
                $output->writeln('<warn>No jobs in that worker group</warn>');

                return -1;
            }

            if (!$ignore_interval) {
                $jobs = [];
                foreach ($group_jobs as $job) {
                    if ($job->isReady()) {
                        $jobs[] = $job;
                    }
                }
            } else {
                $jobs = $group_jobs;
            }

            if (!count($jobs)) {
                if ($verbose) {
                    $output->writeln('No jobs need to run');
                }

                return 0;
            }

            $runner->runJobs($jobs);

        // All jobs
        } else {
            $group_jobs = App::getEntityRepository('DeskPRO:WorkerJob')->findAll();

            if (!$ignore_interval) {
                $jobs = [];
                foreach ($group_jobs as $job) {
                    if ($job->isReady()) {
                        $jobs[] = $job;
                    }
                }
            } else {
                $jobs = $group_jobs;
            }

            if (!count($jobs)) {
                if ($verbose) {
                    $output->writeln('No jobs need to run');
                }

                return 0;
            }

            $runner->runJobs($jobs);
        }
    }
}
