<?php

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
			->addOption('php-exec', 'p', InputOption::VALUE_REQUIRED, 'Runs jobs as child processes using this path to PHP.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
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
