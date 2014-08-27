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
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\Sms\SmsProviderFactory;
use Doctrine\DBAL\Connection;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Processes an outgoing SMS message.
 *
 * The job data contains provider auth details, the message, and to to/from number, and is considered a
 * "dumb" or standalone job.
 *
 * The processor will automatically attempt to send it 5 times before giving up completely.
 */
class OutgoingSmsProcessor extends AbstractJobProcessor
{
	const JOB_TYPE = 'outgoing_sms';

	/**
	 * @var JobQueue
	 */
	private $queue;

	public function __construct(Connection $connection, JobQueue $queue)
	{
		parent::__construct($connection);
		$this->queue = $queue;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(
			array(
				'message',
				'to_number',
				'provider'
			)
		);

		$resolver->setDefaults(
			array(
				'from_number' => null,
				'provider_params' => array()
			)
		);
	}


	public function process(array $data, array $job)
	{
		$provider = SmsProviderFactory::create($data['provider'], $data['provider_params']);
		$sender   = new SmsSender($provider, $data['from_number']);

		$message = new SmsMessage($data['message']);
		$sender->send($data['to_number'], $message);
	}

	public function runExceptionHandler(array $job, \Exception $e)
	{
		$this->markExceptionError(
			$job,
			$this->willRetry($job) ? Job::STATUS_CODE_RETRYING : Job::STATUS_CODE_EXHAUSTED,
			sprintf('SMS Failed (%s/5 attempts)', $job['num_tries'] + 1), // num_tries starts at 0
			$e
		);

		if ($this->willRetry($job)) {
			$this->queue->retryByJobId($job['id'], new \DateTime('now + 2 minutes'));
		}
	}


	private function willRetry($job)
	{
		return $job['num_tries'] < 5;
	}
}
