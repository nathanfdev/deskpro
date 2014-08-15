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

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Sms\DeskPROSmsSender;
use Orb\Sms\SmsException;
use Orb\Sms\SmsMessage;

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
	/**
	 * @var DeskPROSmsSender
	 */
	protected $sms_sender;

	public function __construct(Connection $connection, DeskPROSmsSender $sms_sender)
	{
		parent::__construct($connection);

		$this->sms_sender = $sms_sender;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(array $job)
	{
		$this->touchJob($job);

		try {

			$data = $job['data'];
			$provider = SmsProviderFactory::create($data['provider'], $data['provider_params']);
			$this->sms_sender->setDefaultProvider($provider);
			$this->sms_sender->setDefaultFromNumber($data['from']);
			$message = new SmsMessage($data['message']);
			$this->sms_sender->send($data['to'], $message);

		} catch (SmsException $e) {

			$this->markExceptionError(
				$job,
				sprintf('SMS Failed (%s/5 attempts)', $job['num_tries']+1), // num_tries starts at 0
				$this->willRetry($job) ? 'retrying' : 'exhausted',
				$e
			);

			if ($this->willRetry($job)) {
				$this->scheduleRetry($job, '2 minutes');
			}

		}

		$this->markComplete($job, 'SMS Sent Successfully', '');
	}


	private function willRetry($job)
	{
		return $job['num_tries'] < 5;
	}
}
