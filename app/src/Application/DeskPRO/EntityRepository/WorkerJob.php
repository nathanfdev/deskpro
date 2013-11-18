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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class WorkerJob extends AbstractEntityRepository
{
	/**
	 * @return mixed
	 */

	public function getAll()
	{
		return $this->getEntityManager()->createQuery("
			SELECT j
			FROM DeskPRO:WorkerJob j
			ORDER BY j.interval ASC
		")->execute();
	}

	/**
	 * @param $job_id
	 * @param $priority
	 *
	 * @return array
	 */

	public function getLogs($job_id, $priority)
	{
		return App::getDb()->fetchAll(
			"
			SELECT log_name, session_name, message, priority, UNIX_TIMESTAMP(date_created) AS date_created
			FROM log_items
			WHERE log_name LIKE ? AND priority <= ?
			ORDER BY id DESC
			LIMIT 2000
			",
			array($job_id, $priority)
		);
	}

	/**
	 *
	 */

	public function clearAllLogs()
	{
		App::getDb()->exec("DELETE FROM log_items WHERE log_name LIKE 'worker_job.%'");
	}
}
