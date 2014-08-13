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

use Application\DeskPRO\Entity\Job;
use Doctrine\ORM\EntityManager;

class JobQueue
{
	/**
	 * @var EntityManager
	 */
	private $em;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	public function scheduleJob(Job $job, \DateTime $nextTry = null)
	{
		// schedule the job as waiting directly as we flush it into the db
		$job->status = Job::STATUS_WAITING;

		// if the user did not request a future date, schedule it to be run immediately
		$job->date_next_try = $nextTry ?: new \DateTime();

		// now push it immediately to the queue
		$this->em->persist($job);
		$this->em->flush($job);
	}


	public function schedule($type, array $data, \DateTime $nextTry = null)
	{
		$this->scheduleJob(new Job($type, $data), $nextTry);
	}
}
