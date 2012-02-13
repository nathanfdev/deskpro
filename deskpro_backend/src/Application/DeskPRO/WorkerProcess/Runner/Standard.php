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

/**
 * A standard runner executes all jobs in sequence one at a time
 */
class Standard extends AbstractRunner
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
}
