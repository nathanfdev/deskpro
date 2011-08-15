<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use \Application\DeskPRO\Log\Logger;

/**
 * A worker job is some task that needs to run regularly, or on a schedule.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="worker_jobs")
 */
class WorkerJob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="id", type="string", type="string", length=50)
	 */
	protected $id = null;

	/**
	 * Some runners can be configured to run a number of jobs at a time. This
	 * fields groups them into named bundles.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="worker_group", type="string", length=50, nullable=true)
	 */
	protected $worker_group = null;

	/**
	 * The name of the job.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=100)
	 */
	protected $title = '';

	/**
	 * What it does
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="description", type="string", length=100)
	 */
	protected $description = '';

	/**
	 * The PHP classname of the job executor
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="job_class", type="string", length=100)
	 */
	protected $job_class;

	/**
	 * Options for the job
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="data", type="array", nullable=true)
	 */
	protected $options = array();

	/**
	 * The most ideal interval for this task to run.
	 *
	 * @ORM_Mapping\Column(name="run_interval", type="integer")
	 */
	protected $interval = 3600;

	/**
	 * The last time this job was run
	 *
	 * @var DateTime
	 * @ORM_Mapping\Column(name="last_run_date",type="datetime", nullable=true)
	 */
	protected $last_run_date = null;

	public function createJobObj(Logger $logger)
	{
		$classname = $this->job_class;
		$job = new $classname($logger, $this->options);
		return $job;
	}

	public function isReady()
	{
		if (!$this->interval OR !$this->last_run_date) {
			return true;
		}

		$cut = time() - $this->interval;
		if ($this->last_run_date->getTimestamp() < $cut) {
			return true;
		}

		return false;
	}
}