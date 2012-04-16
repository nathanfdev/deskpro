<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;

class WorkerJobCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:worker-job')
			->addOption('job', 'j', InputOption::VALUE_REQUIRED, 'Run only specific job')
			->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Run only a specific group of jobs')
			->addOption('ignore-interval', 'f', InputOption::VALUE_NONE, 'Always run job(s) even if the job interval has not ellapsed since last run')
			->addOption('daemon', null, InputOption::VALUE_NONE, 'Runs forever. Only "checkable" jobs supported. php-exec option is required.')
			->addOption('php-exec', 'p', InputOption::VALUE_REQUIRED, 'Runs jobs as child processes using this path to PHP.')
			->addOption('options', 'o', InputOption::VALUE_REQUIRED, 'Specify a JSON-encoded array of options to pass to worker jobs');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		#------------------------------
		# Clean up installer error detection
		#------------------------------

		if (file_exists(dp_get_log_dir().'/cron-preboot-errors.log')) {
			@unlink(dp_get_log_dir().'/cron-preboot-errors.log');
		}

		App::getDb()->delete('install_data', array('build' => 1, 'name' => 'cron_run_errors'));

		#------------------------------
		# Run
		#------------------------------

		$cron_id = 'dp-cron';
		if ($input->getOption('job')) {
			$cron_id .= '-' . $input->getOption('job');
		} elseif ($input->getOption('group')) {
			$cron_id .= '-g-' . $input->getOption('group');
		}

		if (!$input->getOption('ignore-interval')) {
			$check = App::getDb()->fetchColumn("SELECT value FROM settings WHERE name = ?", array('core.croncheck.' . $cron_id));
			if ($check) {
				$date = new \DateTime('@'.$check);
				$date_cut = new \DateTime('-15 minutes');
				$date_cut = new \DateTime('-1 minutes');
				$diff = \Orb\Util\Dates::secsToReadable(time() - $date->getTimestamp(), 5);

				if ($date_cut < $date) {
					if ($input->getOption('verbose')) { $output->writeln("$cron_id is still active. Running for {$diff} (since " . $date->format('Y-m-d H:i:s') . ")"); }
					return 0;
				} else {
					$title = "WARNING: Cron ($cron_id) has been active for {$diff}";
					$text = "Cron ($cron_id) has been marked as active for {$diff} (since " . $date->format('Y-m-d H:i:s') . ").\n\n"
							. "This is most likely caused by a fatal error that prevented the runner from resetting the timer.\n\n"
							. "Cron will now resume, but this is a problem you should investigate. Refer to the error log files and contact support@deskpro.com.";
					if (App::getConfig('technical_email')) {
						$message = App::getMailer()->createMessage();
						$message->setSubject($title);
						$message->setBody($text, 'text/plain');
						$message->setTo(App::getConfig('technical_email'));
						App::getMailer()->send($message);
					}

					$output->writeln($title);
					$output->writeln($text);
				}
			}
		}

		App::getDb()->replace('settings', array(
			'name'  => 'core.croncheck.' . $cron_id,
			'value' => time()
		));

		try {
			$step = (int)App::getSetting('core.setup_initial');

			// Only run crom if we've passed initial setup
			if ($step && $step >= 30) {
				$ret = $this->doExecute($input, $output);
			} else {
				$ret = 0;
			}

			App::getDb()->delete('settings', array('name' => 'core.croncheck.' . $cron_id));
			App::getDb()->replace('settings', array('name' => 'core.last_cron_run', 'value' => time()));
			return $ret;
		} catch (\Exception $e) {
			App::getDb()->delete('settings', array('name' => 'core.croncheck.' . $cron_id));
			throw $e;
		}
	}

	protected function doExecute(InputInterface $input, OutputInterface $output)
	{
		$options = null;
		if ($input->getOption('options')) {
			$options = json_decode($input->getOption('options'), true);
			if (!is_array($options)) {
				$output->writeln("<error>The options array is malformed</error>");
				return 1;
			}
		}
		if (!$options) {
			$options = array();
		}

		$verbose = $input->getOption('verbose');

		if (App::getSetting('core.helpdesk_disabled')) {
			if ($verbose) {
				$output->writeln("<info>Helpdesk is currently disabled.</info>");
			}

			return 0;
		}


		$ignore_interval = false;
		if ($input->getOption('ignore-interval')) {
			$ignore_interval = true;
		}

		if ($input->getOption('php-exec')) {
			$cmd = $input->getOption('php-exec') . ' "' . DP_ROOT . '/bin/console-dev" dp:worker-job '.($verbose ? '-v ' : '').'-f -j %job%';

			if ($input->getOption('daemon')) {
				$runner = new \Application\DeskPRO\WorkerProcess\Runner\CheckParallel($cmd);
			} else {
				$runner = new \Application\DeskPRO\WorkerProcess\Runner\ExecParallel($cmd);
			}
		} else {
			$runner = new \Application\DeskPRO\WorkerProcess\Runner\Standard();
		}

		$runner->setJobOptions($options);

		if ($verbose) {
			$runner->setVerbose();
		}

		// A specific job
		if ($input->getOption('job')) {

			$job = App::getEntityRepository('DeskPRO:WorkerJob')->findOneById($input->getOption('job'));
			if (!$job) {
				$output->writeln('<warn>No such job exists</warn>');
				return 1;
			}

			if (!$ignore_interval AND !$job->isReady()) {
				if ($verbose) {
					$output->writeln('Job does not need to run');
				}
				return 0;
			}

			$runner->runJobs(array($job));

		// A group of jobs
		} elseif ($input->getOption('group')) {
			$group_jobs = App::getOrm()->createQuery("
				SELECT j
				FROM DeskPRO:WorkerJob j
				WHERE j.worker_group = ?1
			")->setParameter(1, $input->getOption('group'))->execute();

			if (!count($group_jobs)) {
				$output->writeln('<warn>No jobs in that worker group</warn>');
				return -1;
			}

			if (!$ignore_interval) {
				$jobs = array();
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
				$jobs = array();
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
