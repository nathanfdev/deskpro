<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\ServerCron;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\EntityManager;
use Orb\Util\Dates;

class ServerCron
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	/**
	 * @var int
	 */

	protected $per_page = 100;

	public function __construct(EntityManager $em)
	{
		$this->em       = $em;
	}

	/**
	 * @return array
	 */

	public function getAllForApi()
	{
		$jobs = $this->em->getRepository('DeskPRO:WorkerJob')->getAll();

		$resData = array();

		foreach ($jobs as $key => $job) {

			if ($job instanceof DomainObject) {

				$resData[$key]                      = $job->toApiData(false, true);
				$resData[$key]['interval_readable'] = $job->getIntervalReadable();
				$resData[$key]['next_run_time']     = $job->getNextRunRelativeTime();
			}
		}

		return $resData;
	}

	/**
	 * @return array
	 */

	public function getTimes()
	{
		$last_start = App::getContainer()->getSetting('core.last_cron_start');

		if (!$last_start) {
			$last_start = 0;
		}

		$time_since_start = time() - $last_start;

		$last_run = App::getContainer()->getSetting('core.last_cron_run');

		if (!$last_run) {

			$last_run = 0;
		}

		$time_since_run = time() - $last_run;

		return array(
			'last_run'                  => (int)$last_run,
			'last_run_ms'               => (int)$last_run * 1000,
			'time_since_run'            => (int)$time_since_run,
			'time_since_run_readable'   => Dates::secsToReadable($time_since_run),
			'last_start'                => (int)$last_start,
			'last_start_ms'             => (int)$last_start * 1000,
			'time_since_start'          => (int)$time_since_start,
			'time_since_start_readable' => Dates::secsToReadable($time_since_start),
		);
	}

	/**
	 * @param string $job_id
	 * @param int $priority
	 * @param int $page
	 *
	 * @return array
	 */

	public function getLogs($job_id = null, $priority = null, $page = 1)
	{
		$params = $this->initializeParams($job_id, $priority);
		$from   = ($page - 1) * $this->per_page;

		return $this->em->getRepository('DeskPRO:LogItem')->getCronLogs(
			$params['job_id'],
			$params['priority'],
			$from,
			$this->per_page
		);
	}

	/**
	 * @param string $job_id
	 * @param int $priority
	 *
	 * @return int
	 */

	public function getPagesCount($job_id = null, $priority = null)
	{
		$params = $this->initializeParams($job_id, $priority);

		return $this->em->getRepository('DeskPRO:LogItem')->getCronPagesCount(
			$params['job_id'],
			$params['priority'],
			$this->per_page
		);
	}

	/**
	 * @return bool
	 */

	public function clearAllLogs()
	{
		$this->em->getRepository('DeskPRO:WorkerJob')->clearAllLogs();

		return true;
	}

	/**
	 * @param string $job_id
	 * @param int $priority
	 *
	 * @return array
	 */

	protected function initializeParams($job_id = null, $priority = null)
	{
		if (!$job_id) {

			$job_id = 'worker_job.%';

		} else {

			$job_id = 'worker_job.' . $job_id;
		}

		if (!$priority) {

			$priority = 10;
		}

		return array(
			'job_id'   => $job_id,
			'priority' => $priority,
		);
	}
}