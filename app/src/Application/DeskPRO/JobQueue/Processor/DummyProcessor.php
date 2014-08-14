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
 * @subpackage
 */

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\AbstractJobProcessor;

class DummyProcessor extends AbstractJobProcessor
{
	/**
	 * {@inheritdoc}
	 */
	public function execute(array $job)
	{
		$this->touchJob($job);

//		sleep(15);

		$this->markComplete($job, 'Successfully sent out emails!', "MORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nMORE DETAILS \nok");
	}


	/*
	 * TODO: Move to AbstractProcessor
	 *
	 * @param array  $job
	 * @param        $log_summary
	 * @param        $detailed_logs
	 * @param string $status_code
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function markComplete(array $job, $log_summary, $detailed_logs, $status_code = 'success')
	{
		$this->connection->executeUpdate(
			'
			UPDATE jobs
			SET log_summary = :log_summary,
				log = :detailed_logs,
				status = :completed_status,
				status_code = :status_code,
				date_touch = :date_touch
			WHERE id = :job_id
			',
			array(
				'log_summary' => $log_summary,
				'detailed_logs' => $detailed_logs,
				'completed_status' => Job::STATUS_COMPLETE,
				'status_code' => $status_code,
				'date_touch' => new \DateTime(),
				'job_id' => $job['id']
			),
			array(
				'log_summary' => 'string',
				'detailed_logs' => 'text',
				'completed_status' => 'string',
				'status_code' => 'string',
				'date_touch' => 'datetime',
				'job_id' => 'integer'
			)
		);
	}
}
