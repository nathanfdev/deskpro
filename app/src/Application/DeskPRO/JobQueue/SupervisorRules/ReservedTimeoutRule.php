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

namespace Application\DeskPRO\JobQueue\SupervisorRules;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobSupervisorException;

/**
 * A job should not be "reserved" for more than 30 seconds. Surely the worker that reserved the job is now dead and
 * this needs to be retried again. This is a rule that can fix itself, but will leave an error log as well.
 */
class ReservedTimeoutRule extends AbstractSupervisorRule
{
	protected $date_ago;

	/**
	 * {@inheritdoc}
	 */
	public function check()
	{
		$this->date_ago = new \DateTime('30 seconds ago');

		$query = $this->connection->executeQuery(
			'
			SELECT count(id) as total
			FROM jobs
			WHERE status = :reserved_state
			AND date_touch < :short_time_ago
			',
			array(
				'reserved_state' => Job::STATUS_RESERVED,
				'short_time_ago' => $this->date_ago
			),
			array(
				'reserved_state' => 'string',
				'short_time_ago' => 'datetime'
			)
		);
		$result = $query->fetch();
		if (is_array($result) and array_key_exists('total', $result)) {
			$total = $result['total'];
			if ($total > 0) {
				throw new JobSupervisorException("Found $total reserved jobs that were not executed. Putting them back in the queue.");
			}
		}

	}


	/**
	 * {@inheritdoc}
	 */
	public function attemptToFix()
	{
		// un-reserve the jobs in question
		$count = $this->connection->executeUpdate(
			'
			UPDATE jobs
			SET status = :waiting_status,
			date_touch = :now,
			worker_id = NULL
			WHERE status = :reserved_status
			AND date_touch < :short_time_ago
			',
			array(
				'reserved_status' => Job::STATUS_RESERVED,
				'short_time_ago'  => $this->date_ago,
				'waiting_status'  => Job::STATUS_WAITING,
				'now'             => new \DateTime()
			),
			array(
				'reserved_status' => 'string',
				'short_time_ago'  => 'datetime',
				'waiting_status'  => 'string',
				'now'             => 'datetime'
			)
		);

		return $count > 0;
	}
}
