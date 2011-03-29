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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Log\Logger;

/**
 * A Runner is a thing that knows how to run jobs.
 */
abstract class AbstractRunner
{
	/**
	 * A callback function code can hook into to init loggers
	 * @var callback
	 */
	protected $_init_logger_callback = null;

	/**
	 * Ana rray of already initialized jobs
	 * @var array
	 */
	protected $_job_cache = array();


	public function runJobs($jobs)
	{
		foreach ($jobs as $job) {
			$this->runJob($job);
		}
	}


	
	/**
	 *
	 * @param Entity\WorkerJob $worker_job
	 */
	public function runJob(Entity\WorkerJob $worker_job)
	{
		$job = $this->getJob($worker_job);
		$logger = $job->getLogger();

		$mtime_start = microtime(true);
		$logger->log("Job {$worker_job['id']} start: $mtime_start", Logger::DEBUG, array('flag' => 'job_start'));
		$job->run();
		
		$mtime_end = microtime(true);
		$mtime_total = $mtime_end - $mtime_start;
		$mtime_total = sprintf("%.5f", $mtime_total);
		
		$logger->log("Job {$worker_job['id']} end: $mtime_end ($mtime_total)", Logger::DEBUG, array('flag' => 'job_end'));

		$worker_job['last_run_date'] = new \DateTime();
		App::getOrm()->persist($worker_job);
		App::getOrm()->flush();
	}

	

	/**
	 * Get the job
	 * 
	 * @param Entity\WorkerJob $job_worker
	 * @return Application\DeskPRO\WorkerProcess\Job\AbstractJob
	 */
	public function getJob(Entity\WorkerJob $job_worker)
	{
		if (isset($this->_job_cache[$job_worker['id']])) {
			return $this->_job_cache[$job_worker['id']];
		}

		$logger = $this->getLoggerForWorkerJob($job_worker);
		$job = $job_worker->createJobObj($logger);
		$this->_job_cache[$job_worker['id']] = $job;

		return $job;
	}

	

	/**
	 * Get a logger for a specific job to log its status/debug messages
	 *
	 * @param Entity\WorkerJob $worker_job
	 * @return Logger
	 */
	public function getLoggerForWorkerJob(Entity\WorkerJob $worker_job)
	{
		$logger_session = $worker_job['id'] . '.' . microtime(true);
		$logger = App::createNewLogger('worker', $logger_session);

		$this->_initLogger($logger, $worker_job);
		if ($this->_init_logger_callback) {
			call_user_func($this->_init_logger_callback, $logger, $worker_job);
		}

		return $logger;
	}


	/**
	 * Init the logger with any custom stuff etc
	 */
	public function _initLogger(Logger $logger, Entity\WorkerJob $worker_job)
	{
		// Empty hook method to init a logger with custom writers or filters
	}

	

	/**
	 * Set a custom callback function that helps init the logger.
	 */
	public function setCustomLoggerInit($fn)
	{
		$this->_init_logger_callback = $fn;
	}
}