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

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\Processor\DummyProcessor;
use Application\DeskPRO\JobQueue\Processor\OutgoingSmsProcessor;

/**
 * The Job Router is responsible for instantiating the JobProcessor for a job and executing it
 */
class JobRouter
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $connection;


	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}


	/**
	 * @param array $job an array of a "jobs" row from dbal
	 */
	public function handle(array $job)
	{
		try {
			// TODO: maybe make processors reusable?
			$processor = $this->findProcessor($job);
			$processor->execute($job);
		} catch (\Exception $e) {
			$this->markException($job, $e);
		}
	}


	/**
	 * Does the actual job array -> job processor mapping and returns an instantiated JobProcessorInterface
	 *
	 * @return DummyProcessor
	 */
	private function findProcessor(array $job)
	{
		// TODO: route these more intelligently, probably have the processors instantiated outside this class, injected
		switch ($job['type']) {
			case 'outgoing_sms':
				return new OutgoingSmsProcessor($this->connection);
			default:
				throw new JobQueueException(sprintf('No processor found for "%s"', $job['type']));
		}
	}


	/**
	 * We should not have to do this at this layer, but we will if needed.
	 *
	 * Processors are encouraged to handle their own errors gracefully.
	 *
	 * @param array $job the job row from the dbal
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	protected function markException(array $job = null, \Exception $e)
	{
		if (is_array($job) && array_key_exists('id', $job)) {
			App::getDb()->executeUpdate(
				'
				UPDATE jobs
				SET status = :error_status,
					date_touch = :date_now,
					date_last_try = :date_now,
					log = :error_log,
					log_summary = :log_summary,
					num_tries = num_tries + 1,
					has_warning = 1
				WHERE id = :job_id
				',
				array(
					'error_status' => Job::STATUS_ERROR,
					'date_now'     => new \DateTime(),
					'job_id'       => $job['id'],
					'error_log'    => $e->getMessage() . "\n\n\n" . $e->getTraceAsString(),
					'log_summary'  => 'A system error occurred',
				),
				array(
					'error_status' => 'string',
					'date_now'     => 'datetime',
					'job_id'       => 'integer',
					'error_log'    => 'text',
					'log_summary'  => 'string',
				)
			);
		}
	}
}
