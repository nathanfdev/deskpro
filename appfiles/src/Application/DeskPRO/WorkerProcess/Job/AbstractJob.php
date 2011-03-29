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

namespace Application\DeskPRO\WorkerProcess\Job;

use \Application\DeskPRO\Log\Logger;

/**
 * A job completes some specific processing task.
 */
abstract class AbstractJob
{
	const DEFAULT_INTERVAL = 3600;

	protected $options = array();

	/**
	 * @var Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	final public function __construct(Logger $logger, array $options = null)
	{
		if ($options) {
			$this->options = $options;
		}
		$this->logger = $logger;
	}



	/**
	 * Run the task
	 */
	abstract public function run();


	
	/**
	 * Log a status message. These should include information about how many records
	 * processed etc.
	 * 
	 * @param string $message
	 * @param array $details
	 */
	public function logStatus($message, array $details = array())
	{
		$details['flag'] = 'status';
		$this->logger->log($message, Logger::INFO, $details);
	}


	
	/**
	 * Get the logger for this job
	 *
	 * @return Application\DeskPRO\Log\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}