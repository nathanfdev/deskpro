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
 * @subpackage
 */

namespace Application\DeskPRO\JobQueue\SupervisorRules;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobSupervisorException;

/**
 * A job should not be in the "processing" state for more than 5 minutes, signal an error as there is unfinished work!
 */
class ProcessingTimeoutRule extends AbstractSupervisorRule
{
	/**
	 * {@inheritdoc}
	 */
	public function check()
	{
		$date = new \DateTime('5 minutes ago');

		$query = $this->connection->executeQuery(
			'
			SELECT count(id) as total
			FROM jobs
			WHERE status = :processing_state
			AND date_touch < :five_mins_ago
			',
			array(
				'processing_state' => Job::STATUS_PROCESSING,
				'five_mins_ago' => $date
			),
			array(
				'processing_state' => 'string',
				'five_mins_ago' => 'datetime'
			)
		);
		$result = $query->fetch();
		if (is_array($result) and array_key_exists('total', $result)) {
			$total = $result['total'];
			if ($total > 0) {
				throw new JobSupervisorException("Found $total idle jobs that have been processing for 5+ minutes.");
			}
		}

	}


	/**
	 * {@inheritdoc}
	 */
	public function attemptToFix()
	{
		// we might want to retry jobs like this up to 5 "num_tries" or something
		return false;
	}
}
