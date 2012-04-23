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

use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Mail\Transport\DelegatingTransport;

/**
 * Goes through queued messages
 */
class SendmailQueue extends AbstractJob
{
	const DEFAULT_INTERVAL = 60;

	protected $count_success;
	protected $count_failed;

	public function run()
	{
		return;
		$db_proc = new DatabaseQueueProcessor();
		$db_proc->processQueue(array($this, '_sendMessage'));

		$total = $this->count_success + $this->count_failed;
		if ($total) {
			$this->logStatus("Processed {$total} emails in queue. {$this->count_success} successful, {$this->count_failed} failed.");
		}
	}

	public function _sendMessage($message)
	{
		$mailer = App::getMailer();
		if ($mailer->getTransport() instanceof DelegatingTransport) {
			$mailer->getTransport()->disableQueue();
		}

		$success = $mailer->send($message);

		if ($mailer->getTransport() instanceof DelegatingTransport) {
			$mailer->getTransport()->enableQueue();
		}

		if (!$success) {
			$this->count_failed++;
			return DatabaseQueueProcessor::PROCESS_FAILURE;
		}

		$this->count_success++;
		return DatabaseQueueProcessor::PROCESS_SUCCESS;
	}
}
