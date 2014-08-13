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
 * @subpackage JobQueue
 */

namespace Application\DeskPRO\JobQueue;

use Doctrine\DBAL\Connection;

/**
 * Helper methods available to children, encouraged to extend this when creating a job processor (but not required to).
 */
abstract class AbstractJobProcessor implements JobProcessorInterface
{
	/**
	 * @var Connection
	 */
	protected $connection;


	/**
	 * {@inheritdoc}
	 */
	abstract public function execute(array $job);


	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}


	/**
	 * Touch the job
	 *
	 * @param $job
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function touchJob(array $job)
	{
		$this->connection->executeUpdate(
			'
			UPDATE jobs
			SET date_touch = :date_now
			WHERE id = :job_id
			',
			array(
				'date_now' => new \DateTime(),
				'job_id' => $job['id']
			),
			array(
				'date_now' => 'datetime',
				'job_id' => 'integer'
			)
		);
	}
}
