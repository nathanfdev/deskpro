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

use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;

use Application\DeskPRO\App;

/**
 * This runner goes through all the cron jobs and executes them in their own process
 */
class ChildProcess extends AbstractRunner
{
	protected $is_verbose = false;
	public function setVerbose()
	{
		$this->is_verbose = true;
	}

	public function _initLogger(Logger $logger, Entity\WorkerJob $worker_job)
	{
		if ($this->is_verbose) {
			$out_writer = new \Orb\Log\Writer\Stream('php://stdout');
			$logger->addWriter($out_writer);
		}
	}

		/**
	 *
	 * @param Entity\WorkerJob $worker_job
	 */
	public function runJob(Entity\WorkerJob $worker_job)
	{
		$cmd = App::getConfig('php_path') . ' cmd.php dp:worker-job -j='.$worker_job->id;

		$process = new \Symfony\Component\Process\Process($cmd, DP_WEB_ROOT);
		$process->run();
	}
}
