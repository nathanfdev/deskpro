<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Runner;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Log\Logger;

/**
 * This runner forks and runs several jobs in parallel using exec().
 */
class ExecParallel extends Standard
{
	protected $_max_childs = 4;
	protected $_cmd_pattern;

	protected $_running_children = array();

	public function __construct($cmd_pattern, $max_children = 4)
	{
		$this->_cmd_pattern = $cmd_pattern;
		$this->_max_childs = $max_children;

		$this->init();
	}

	protected function init()
	{
		// empty hook method
	}


	public function runJobs($jobs)
	{
		if (!count($jobs)) {
			return;
		}

		if (!is_array($jobs)) {
			$got_jobs = $jobs;
			$jobs = array();

			foreach ($got_jobs as $j) $jobs[] = $j;
			unset($got_jobs);
		}

		// Get chunks that each child will work on
		$chunks = array_chunk($jobs, ceil(count($jobs) / $this->_max_childs));

		// Fork for each chunk
		foreach ($chunks as $chunk) {
			$this->_forkChunk($chunk);
		}

		// Wait for each child to finish
		do {
			if ($pid = pcntl_wait($status)) {
				$job_id = array_search($pid, $this->_running_children);
				unset($this->_running_children[$job_id]);
			}
			usleep(500000);
		} while ($this->_running_children);
	}

	protected function _forkChunk(array $jobs)
	{
		$pid = pcntl_fork();
		if ($pid === -1) {
			throw new \Exception('Could not fork');
		} elseif ($pid) {
			$this->_running_children[] = $pid;
		} else {
			$this->runChunk($jobs);
			exit(0);
		}
	}

	public function runChunk(array $jobs)
	{
		// If we got here, we're the child and can process the jobs
		foreach ($jobs as $job) {
			$this->runJob($job);
		}
	}
	
	public function runJob(Entity\WorkerJob $job)
	{
		passthru($this->getCmdForJob($job));
	}

	protected function getCmdForJob(Entity\WorkerJob $job)
	{
		$cmd = $this->_cmd_pattern;
		$cmd = str_replace('%job%', $job['id'], $cmd);

		return $cmd;
	}
}