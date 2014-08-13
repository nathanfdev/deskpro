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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobRouter;

class JobWorker extends AbstractJob
{
	const DEFAULT_INTERVAL = 1;

	/**
	 * @var string worker ID string
	 */
	protected $workerId;

	/**
	 * @var JobRouter the router
	 */
	protected $jobRouter;

	public function run()
	{
		// a unique ID for this particular worker
		$this->workerId = uniqid();

		// create the JobRouter for this worker instance
		$this->jobRouter = new JobRouter(App::getDb());

		#------------------------------
		# loop for 60 seconds
		#------------------------------
		$workerStartTime = time();
		$workerEndTime = $workerStartTime + 60;
		while (time() <= $workerEndTime) {

			try {
				$this->executeNextJob();
			} catch (\Exception $e) {
				// doing nothing after this catch is better than exiting the process
				throw $e;
			}

			// don't burn out the CPU cycles. CPU can get to 100% on an infinite loop.
			// TODO: probably make this a usleep()
			sleep(1);
		}
	}


	/**
	 * Pop the next job, mark it as processing, and attempt to handle it
	 */
	protected function executeNextJob()
	{
		$job = $this->popJob();

		// can only continue if we have a job array with an ID
		if (is_array($job) && array_key_exists('id', $job)) {
			$this->markProcessing($job);

			try {
				$this->jobRouter->handle($job);
			} catch (\Exception $e) {
				// the router layer and processor layer are responsible for recording failures and retries etc on their own
				// router is supposed to handle all situations and catch all errors and never throw
				// this is here to attempt to keep the queue moving in case of what should be next-to-impossible situations
				return;
			}
		}
	}


	/**
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 *
	 * @return array|null an array of the job, or null if none available to work on
	 */
	protected function popJob()
	{
		/**
		 * Reserve the next available job
		 */
		$result = App::getDb()->executeUpdate(
			'
			UPDATE jobs
			SET worker_id = :this_worker_id,
				status = :reserved_status
			WHERE status = :waiting_status
			AND date_next_try <= :date_now
			AND worker_id IS NULL
			ORDER BY priority DESC,
					 date_next_try ASC
			LIMIT 1
			',
			array(
				'this_worker_id' => $this->workerId,
				'reserved_status' => Job::STATUS_RESERVED,
				'waiting_status' => Job::STATUS_WAITING,
				'date_now' => new \DateTime()
			),
			array(
				'this_worker_id' => 'string',
				'reserved_status' =>'string',
				'waiting_status' => 'string',
				'date_now' => 'datetime'
			)
		);


		/**
		 * Fetch the next job reserved for me
		 */
		$job = App::getDb()->fetchAssoc(
			'
			SELECT *
			FROM jobs
			WHERE worker_id = :this_worker_id
			AND status = :reserved_status
			',
			array(
				'reserved_status' => Job::STATUS_RESERVED,
				'this_worker_id' => $this->workerId
			),
			array(
				'reserved_status' => 'string',
				'this_worker_id' => 'string',
			)
		);

		return $job ?: null;
	}


	/**
	 * Worker calls this before executing a job
	 *
	 * @param array $job the job row from the dbal
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	protected function markProcessing(array $job)
	{
		if (is_array($job) && array_key_exists('id', $job)) {
			App::getDb()->executeUpdate(
				'
				UPDATE jobs
				SET status = :processing_status,
					date_touch = :date_now,
					date_last_try = :date_now,
					num_tries = num_tries + 1
				WHERE id = :job_id
				',
				array(
					'processing_status' => Job::STATUS_PROCESSING,
					'date_now'          => new \DateTime(),
					'job_id'            => $job['id']
				),
				array(
					'processing_status' => 'string',
					'date_now'          => 'datetime',
					'job_id'            => 'integer'
				)
			);
		}
	}
}
