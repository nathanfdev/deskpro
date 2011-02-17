<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use \Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Log\Logger;
use \Application\DeskPRO\Mail\Transport\DelegatingTransport;

/**
 * Goes through queued messages
 */
class SendmailQueue extends AbstractJob
{
	protected $count_success;
	protected $count_failed;

	public function run()
	{
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