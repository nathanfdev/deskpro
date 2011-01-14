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
 * This runner continuously runs in a loop, used by the CheckableInterface items
 */
class CheckParallel extends ExecParallel
{
	protected $job_workers = array();

	protected $_should_stop = false;

	protected function init()
	{
		pcntl_signal(SIGTERM, array($this, '_handleTerm'));
		pcntl_signal(SIGINT, array($this, '_handleTerm'));
	}

	/**
	 * Called when we get term/int signals, which means we want to quit.
	 * So when that happens we should stop running jobs.
	 */
	public function _handleTerm()
	{
		$this->_should_stop = true;
	}
	
	public function runChunk(array $worker_jobs)
	{
		while (true) {

			if ($this->_should_stop) return;

			$usleep = 200000;

			foreach ($worker_jobs as $worker_job) {

				if ($this->_should_stop) return;

				$job = $this->getJob($job_worker);
				if (!($job instanceof \Application\DeskPRO\WorkerProcess\Job\CheckableInterface)) {
					continue;
				}
				if ($job->isReady()) {
					$this->runJob($job);
				}

				$usleep = max($usleep, $job->getReadyCheckDelay());
			}

			usleep($usleep);
		}
	}
}