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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\Log\Logger;

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
	 * @ORM_Mapping\Column(name="description", type="string", length=255)
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
	 * The most feedbackl interval for this task to run.
	 *
	 * @ORM_Mapping\Column(name="run_interval", type="integer")
	 */
	protected $interval = 3600;

	/**
	 * The last time this job was run
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="last_run_date",type="datetime", nullable=true)
	 */
	protected $last_run_date = null;


	/**
	 * Get the date of the next run
	 *
	 * @return \DateTime
	 */
	public function getNextRunDate()
	{
		if ($this->last_run_date) {
			$d = clone $this->last_run_date;
		} else {
			$d = new \DateTime();
		}

		$d->add(new \DateInterval('PT' . $this->interval . 'S'));
		return $d;
	}


	/**
	 * Get interval in readable Enlgish
	 *
	 * @return string
	 */
	public function getIntervalReadable()
	{
		return \Orb\Util\Dates::secsToReadable($this->interval, 5, 'short');
	}


	/**
	 * @param \Application\DeskPRO\Log\Logger $logger
	 * @return \Application\DeskPRO\WorkerProcess\Job\AbstractJob
	 */
	public function createJobObj(Logger $logger)
	{
		$classname = $this->job_class;
		$job = new $classname($logger, $this->options);
		return $job;
	}


	/**
	 * @return bool
	 */
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
