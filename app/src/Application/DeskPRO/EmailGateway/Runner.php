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
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\NoopConfig;
use Application\DeskPRO\EmailGateway\Exception\ProcessingException;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\EmailGateway\Fetcher;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;
use Orb\Util\Util;

/**
 * This runs collection and processsing in accounts
 */
class Runner
{
	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	private $logger;

	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
	 */
	private $account_manager;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount[]
	 */
	private $accounts;

	/**
	 * @var bool
	 */
	private $enable_retry_scheduling = true;

	/**
	 * @var int
	 */
	private $max_retry_attempts = 3;

	/**
	 * @var \Orb\Log\Writer\ArrayWriter
	 */
	private $log_messages;

	/**
	 * When non-0, sets the PHP time limit per iteration
	 *
	 * @var int
	 */
	private $set_time_limit = 0;

	/**
	 * When non-0, sets when the email loop will break early when
	 * DP_START_TIME has gone over.
	 *
	 * @var int
	 */
	private $soft_time_limit = 0;

	/**
	 * When non-0, sets when the email loop will break early
	 * when this many messages have been processed;
	 *
	 * @var int
	 */
	private $message_limit = 0;

	/**
	 * @var int
	 */
	private $message_count = 0;

	/**
	 * @var array
	 */
	protected $from_headers;

	public function __construct()
	{
		$this->logger = new \Application\DeskPRO\Log\Logger();
		$this->account_manager = App::$container->getEmailAccountManager();
	}


	/**
	 * Set the PHP time limit for a single message. This uses set_time_limit()
	 * and resets it every iteration.
	 *
	 * This is used as an infinite-loop type preventative measure. PHP will halt
	 * the script, and whatever message that was being processed will be stuck in the 'inserted'
	 * state.
	 *
	 * @param int $time_limit
	 */
	public function setPhpTimeLimit($time_limit)
	{
		$this->set_time_limit = $time_limit;
	}


	/**
	 * @param boolean $enabled
	 */
	public function setRetryScheduling($enabled = true)
	{
		$this->enable_retry_scheduling = $enabled;
	}


	/**
	 * @param int $time_limit
	 */
	public function setSoftTimeLimit($time_limit)
	{
		$this->soft_time_limit = $time_limit;
	}


	/**
	 * @param int $limit
	 */
	public function setMessageLimit($limit)
	{
		$this->message_limit = $limit;
	}


	/**
	 * @param $logger \Application\DeskPRO\Log\Logger
	 */
	public function setLogger(\Application\DeskPRO\Log\Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * Set the accounts to process
	 *
	 * @param EmailAccount[] $accounts
	 */
	public function setAccounts(array $accounts)
	{
		$this->accounts = $accounts;
	}


	/**
	 * Load accounts from the database
	 *
	 * @param bool $include_disabled True to also include disabled account
	 */
	public function loadAccountsFromDb($include_disabled = false)
	{
		if ($include_disabled) {
			$this->accounts = $this->account_manager->getAllAccounts('with_fetcher');
		} else {
			$this->accounts = $this->account_manager->getAllActiveAccounts('with_fetcher');
		}
	}


	/**
	 * @param int $time_limit The max time spent processing email before we break.
	 */
	public function execute($time_limit = 0)
	{
		$exec_start = time();

		if (!$time_limit) {
			$time_limit = 9999999999;
		}

		$this->logger->logDebug("Time limit: " . $time_limit);

		if ($this->accounts) {
			foreach ($this->accounts as $account) {

				// only tickets supported at the moment
				if ($account->account_type != 'tickets') {
					continue;
				}

				App::getDb()->avoidTimeout();
				$this->executeAccount($account, $time_limit);

				$time_so_far = time() - $exec_start;
				$this->logger->logDebug("Time taken so far: " . $time_so_far);

				if ($time_limit && $time_so_far >= $time_limit) {
					$this->logger->logDebug("Breaking, out of time");
					break;
				}
			}
		}
	}


	/**
	 * @param OptionsArray $result
	 * @param bool         $is_retry
	 * @return bool
	 */
	private function verifyCreatedObject(OptionsArray $result, $is_retry = false)
	{
		$this->logger->logDebug("Verifying created object...");

		if ($is_retry) {
			$this->logger->logDebug("-> Checking again");
		}

		if ($result->created_object_type == 'no_value') {
			$this->logger->logDebug('-> NoValue was returned (note: that is a valid return)');
			return true;
		}

		$id = $result->created_object_id;
		if (!$id) {
			$this->logger->logWarn("--> No object ID");
			return false;
		}

		$check_result = false;

		switch ($result->created_object_type) {
			case 'ticket':
				$this->logger->logDebug("--> Verifying ticket {$id}");
				$t = App::$container->getDb()->fetchColumn("SELECT id FROM tickets WHERE id = ?", array($id));
				if ($t) {
					$this->logger->logInfo("--> Ticket OKAY");
					$check_result = true;
				} else {
					$this->logger->logWarn("--> Ticket DOES NOT exist");
					$check_result = false;
				}
				break;

			case 'ticket_message':
				$this->logger->logDebug("--> Verifying ticket message {$id}");
				$t = App::$container->getDb()->fetchColumn("SELECT id FROM tickets_messages WHERE id = ?", array($id));
				if ($t) {
					$this->logger->logInfo("--> Ticket message OKAY");
					$check_result = true;
				} else {
					$this->logger->logWarn("--> Ticket message DOES NOT exist");
					$check_result = false;
				}
				break;

			default:
				$this->logger->logWarn("--> Unknown object type: {$result->created_object_type}");
				return false;
		}

		if (!$check_result && !$is_retry) {
			sleep(1);
			return $this->verifyCreatedObject($result, true);
		}

		return $check_result;
	}


	/**
	 * Called after all transactions are closed. This is a double-check
	 * to make sure a source has the proper status applied to it, even in cases
	 * where the doctrine entity manager is closed due to some critical error.
	 *
	 * @param EmailSource $source
	 * @param array $manual_set
	 */
	private function ensureSourceStatus(EmailSource $source, array $manual_set = null)
	{
		global $DP_SET_SOURCE_STATUS;
		if (!$DP_SET_SOURCE_STATUS) {
			$DP_SET_SOURCE_STATUS = array();
		}

		$db = App::$container->getDb();
		$id = $source->id;

		if (!isset($DP_SET_SOURCE_STATUS[$id])) {
			$DP_SET_SOURCE_STATUS[$id] = array();
		}

		$DP_SET_SOURCE_STATUS[$id] = array_merge($DP_SET_SOURCE_STATUS[$id], array(
			'status'      => $source->status,
			'error_code'  => $source->error_code,
			'source_info' => serialize($source->source_info ?: array())
		));

		if ($manual_set) {
			$DP_SET_SOURCE_STATUS[$id] = array_merge($DP_SET_SOURCE_STATUS[$id], $manual_set);
		}

		\DpShutdown::add(function() use ($db, $id) {

			global $DP_SET_SOURCE_STATUS;
			if (empty($DP_SET_SOURCE_STATUS[$id])) {
				return;
			}

			$set = $DP_SET_SOURCE_STATUS[$id];

			// These must not be in an active trans
			try {
				while ($db->isTransactionActive()) {
					$db->commit();
				}
			} catch (\Exception $e) {}

			try {
				$db->update('email_sources', $set, array('id' => $id));
			} catch (\Exception $e) {
				KernelErrorHandler::logException($e);
			}
		});
	}


	/**
	 * Executes a single source. Good for re-processing.
	 *
	 * @param \Application\DeskPRO\Entity\EmailSource $source
	 * @param AbstractReader $reader
	 * @throws \Exception
	 * @return bool
	 */
	public function executeSource(EmailSource $source, AbstractReader $reader = null)
	{
		if (!$this->log_messages) {
			$this->log_messages = new \Orb\Log\Writer\ArrayWriter();
			$this->log_messages->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());
			$this->logger->addWriter($this->log_messages);
		}

		$is_in_trans = App::getDb()->isTransactionActive();
		if ($is_in_trans) {
			$this->logger->logWarn('Note: Called within a transaction');
		} else {
			$this->logger->logDebug('Note: Not called within a transaction');
		}

		$this->log_messages->clear();

		$previous_log_text = null;
		if ($source->log_blob) {
			try {
				$previous_log_text = App::$container->getBlobStorage()->copyBlobRecordToString($source->log_blob);
			} catch (\Exception $e) {}
		}

		$source->exec_count++;

		$this->logger->logDebug('Executing Source ' . $source->getId());
		$this->logger->logDebug('Attempt: ' . $source->exec_count);

		// Attempt to detect if we should break due to memory
		$mem = memory_get_usage();
		$avail = deskpro_install_check_parseinisize(@ini_get('memory_limit'));
		if ($mem && $mem > 0 && $avail && $avail > 0) {
			$remain = $avail - $mem;
			$min = max(10485760, $source->blob->filesize * 4);
			$room = $remain - $min;

			$this->logger->logDebug(sprintf("Memory Used: %d    Memory Max: %d    Est Memory Required: %d    Est Memory After: %d", $mem, $avail, $min, $room));

			if ($remain < $min) {
				$this->logger->log(sprintf("Detected that we are at the memory limit, quitting run"), 'debug');
				throw new ProcessingException("Detected that we are at the memory limit", ProcessingException::MEMORY_LIMIT);
			}
		}

		// Mark as processing now
		$this->logger->logDebug('Marking source as processing');
		$source->status = 'processing';
		App::getOrm()->persist($source);
		App::getOrm()->flush();

		$allow_retry = $this->enable_retry_scheduling;
		$this->logger->logInfo("Retrying is " . ($allow_retry ? "on" : "off"));
		if ($allow_retry && $source->exec_count >= $this->max_retry_attempts) {
			$allow_retry = false;
			$this->logger->logInfo("--> Retrying turned off, max count reached: {$source->exec_count} >= {$this->max_retry_attempts}");
		}

		$this->logger->logDebug("Running processors");
		$runner_exec = new RunnerExecSource(
			$source,
			$reader,
			$this->account_manager,
			$this->logger
		);
		$runner_exec->setFromHeaders($this->getFromHeaders());

		$did_rollback = false;
		$do_retry = false;
		try {
			$result = $runner_exec->run();
			App::$container->getEm()->flush();
			$this->logger->logDebug("--> Processors complete");

			if (!$is_in_trans && App::getDb()->isTransactionActive()) {
				$this->logger->log("WARNING: Unclosed transaction!", 'info');
				$e = new \RuntimeException("WARNING: Unclosed transaction");
				KernelErrorHandler::logException($e);
				while (App::getDb()->isTransactionActive()) {
					App::getDb()->commit();
				}
			}
		} catch (\Exception $e) {
			$this->logger->logDebug("--> Processor exception: {$e->getCode()} {$e->getMessage()}");
			$result = array(
				'status' => 'error',
				'error_code' => 'server_error',
				'source_info' => array(
					'exception' => get_class($e),
					'message'   => $e->getMessage(),
					'code'      => $e->getCode(),
					'trace'     => KernelErrorHandler::formatBacktrace($e->getTrace())
				)
			);

			if ($allow_retry) {
				$do_retry = true;
				if (strpos(strtolower($e->getMessage()), 'deadlock') !== false) {
					KernelErrorHandler::logException($e, true);
				}
			} else {
				$this->logger->logWarn("Not trying again (allow_retry is false)");
				KernelErrorHandler::logException($e, true);
			}

			if (App::getDb()->isTransactionActive()) {
				App::getDb()->rollback();
				$did_rollback = true;
			}
		}

		$result = new OptionsArray($result);

		// Verify object
		if ($result->status == 'okay') {
			if (!$this->verifyCreatedObject($result)) {
				$new_result = new OptionsArray(array(
					'status'      => 'error',
					'error_code'  => 'server_error',
					'source_info' => array(
						'Failed to verify created object',
						'Expected: ' . $result->created_object_type . ' ' . $result->created_object_id,
					)
				));

				$result = $new_result;

				if ($allow_retry) {
					$do_retry = true;
				} else {
					$this->logger->logWarn("Not trying again (allow_retry is false)");
				}
			}
		}

		if ($reader && $subj = $reader->getSubject()->getSubjectUtf8()) {
			$source->header_subject = $subj;
		}

		switch ($result->status) {
			case 'okay':
				$return_result       = true;
				$source->status      = 'complete';
				$source->error_code  = null;
				$source->source_info = $result->source_info ?: array();
				$source->object_type = $result->created_object_type;
				$source->object_id   = $result->created_object_id;
				$this->logger->logInfo("Status: COMPLETE {$source->error_code}");
				break;

			case 'rejected':
				$return_result       = true;
				$source->status      = 'rejected';
				$source->error_code  = $result->error_code ?: 'server_error';
				$source->source_info = $result->source_info ?: array();
				$this->logger->logError("Status: REJECTED {$source->error_code}");
				break;

			case 'error':
				$return_result       = false;
				$source->status      = 'error';
				$source->error_code  = $result->error_code ?: 'server_error';
				$source->source_info = $result->source_info ?: array();
				$this->logger->logError("Status: ERROR {$source->error_code}");
				break;

			default:
				$return_result       = true;
				$source->status      = 'error';
				$source->error_code  = $result->error_code ?: 'server_error';
				$source->source_info = $result->source_info ?: array();
				$this->logger->logWarn("Unknown status type: {$result->status}");
				break;
		}

		if ($do_retry) {
			$this->logger->logInfo("Scheduling a retry -- status set to inserted");
			$source->status = 'retry';
		}

		$this->ensureSourceStatus($source);

		$log_messages = $this->log_messages->getMessagesAsString();

		if ($previous_log_text) {
			$log_messages = $previous_log_text . "\n\n\n" . str_repeat('-', 80) . "\n\n\n" . $log_messages;
		}

		try {
			$this->logger->logDebug("Saving log blob...");
			$log_blob_row = App::$container->getBlobStorage()->createBlobRowFromString($log_messages, 'email-process.log', 'plain/text');
			$this->logger->logInfo("Log blob {$log_blob_row['id']}");

			$this->ensureSourceStatus($source, array('log_blob_id' => $log_blob_row['id']));

			if (!$did_rollback) {
				$blob = App::$container->getEm()->find('DeskPRO:Blob', $log_blob_row['id']);
				$source['log_blob'] = $blob;
				try {
					App::$container->getEm()->persist($blob);
					App::$container->getEm()->flush();
				} catch (\Exception $e) {}
			}

			$saved_log = true;
		} catch (\Exception $e) {
			$saved_log = false;
		}

		if (!$saved_log) {
			$this->logger->logDebug("Couldnt save log blob, saving to source info instead");
			$source->source_info = array_merge($source->source_info, array('log' => $log_messages));
			$this->ensureSourceStatus($source);
		}

		$source->clearRawSource();

		App::getOrm()->detach($source);
		$source = null;

		if ($reader) {
			$reader->_kill();
			$reader = null;
		}

		$this->logger->logDebug("ALL DONE");

		$this->log_messages->clear();

		gc_collect_cycles();

		return $return_result;
	}


	/**
	 * Execute an account
	 *
	 * $time_limit is the max time before the while loop breaks. The method will usually continue to process mail
	 * until there is no email left. If you specify a time limit then the process will break after $time_limit seconds.
	 * Note this check is done after processing of a message, it does not abort. This means that it's possible the time
	 * limit will be exceeded (e.g., time limit of 10, message starts processing at 9 seconds so it continues).
	 *
	 * @param \Application\DeskPRO\Entity\EmailAccount $account
	 * @param int $time_limit The max time spent processing email before we break.
	 * @throws \Exception
	 */
	public function executeAccount(EmailAccount $account, $time_limit = 0)
	{
		gc_enable();

		$this->logger->log("Start processing {$account['address']} {$account['account_type']}", 'info');
		$start_time = microtime(true);

		/** @var $fetcher \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher */
		$fetcher = $this->createFetcher($account);
		$fetcher->setLogger($this->logger);

		$this->logger->log("Fetcher type: " . Util::getBaseClassname($fetcher), 'info');

		$max_size = App::getSetting('core.gateway_max_email');
		if (!$max_size) {
			$max_size = 20971520;
		}
		$fetcher->setMaxSize($max_size);

		$exec_start = time();
		$source = null;
		$created_obj = null;
		$reader = null;

		$inserted_source_ids = App::getDb()->fetchAllCol("
			SELECT id FROM
			email_sources
			WHERE status IN ('inserted', 'retry') AND email_account_id = ?
			ORDER BY id ASC
		", array($account->getId()));

		$this->logger->logDebug(sprintf("%d inserted messages being processed first", count($inserted_source_ids)));

		$processed_source_ids = array();

		while (true) {
			// Make sure any records are flusehd
			App::getOrm()->flush();

			// Protection against nested transactions.
			// This should not be needed, but its a safety against unclosed transactions.
			// Without it, a mistake somewhere down the line can result in an entire
			// process of emails being rolledback.
			if (App::getDb()->isTransactionActive()) {
				$this->logger->log("WARNING: Unclosed transaction!", 'info');
				$e = new \RuntimeException("WARNING: Unclosed transaction. Sources processed: " . implode(', ', $processed_source_ids));
				KernelErrorHandler::logException($e);
				while (App::getDb()->isTransactionActive()) {
					App::getDb()->commit();
				}
			}

			if ($this->message_limit) {
				if ($this->message_count >= $this->message_limit) {
					$this->logger->logWarn(sprintf("Hit message limit, breaking :: Processed %d messages", $this->message_count));
					break;
				}
			}

			$m = memory_get_usage();

			if ($next_inserted_id = array_shift($inserted_source_ids)) {
				$this->logger->logDebug(sprintf("Processing next inserted message: %d", $next_inserted_id));
				$source = App::getOrm()->find('DeskPRO:EmailSource', $next_inserted_id);
			} else {
				try {
					$source = $fetcher->readNext();
					if (!$source) {
						$this->logger->logDebug("No more messages in inbox");

						// If this is the first time we've reached the end
						// save a start date to the account
						if (!$account->date_read_start) {
							$account->date_read_start = new \DateTime("-10 days");
							App::getOrm()->persist($account);
							App::getOrm()->flush($account);
						}

						break;
					}
				} catch (\Exception $e) {
					$this->logger->log(sprintf("readNext exception: %s", $e->getMessage()), 'info');
					KernelErrorHandler::logException($e, false);
					break;
				}
			}

			$processed_source_ids[] = $source->id;

			if (!$this->log_messages) {
				$this->log_messages = new \Orb\Log\Writer\ArrayWriter();
				$this->logger->addWriter($this->log_messages);
			}

			$this->log_messages->clear();

			if ($this->set_time_limit) {
				@set_time_limit($this->set_time_limit);
			}

			$this->logger->log("[Account {$account['id']}] Read source ID {$source['id']}", 'debug');

			// Already marked as an error (e.g., message too big) so we dont
			// process it through the account handlers
			if ($source->status == 'error') {
				$this->logger->log(sprintf("Source marked as error :: %s", $source->error_code), 'debug');

				// Send alert to user
				if ($source->error_code == EmailSource::ERR_MESSAGE_TOO_BIG) {
					$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
					$reader->setRawSource($source->headers . "\n\nBogus Body\n");
					$from_email = $reader->getFromAddress()->getEmail();
					$subject    = $reader->getSubject()->getSubjectUtf8();

					if ($from_email and $subject) {
						$this->logger->log('Sending too-big email response', 'debug');

						$message = App::getMailer()->createMessage();
						$message->setTemplate('DeskPRO:emails_user:email-too-big.html.twig', array(
							'subject'  => $subject,
							'max_size' => Numbers::filesizeDisplay($max_size)
						));
						$message->setTo($from_email);
						App::getMailer()->send($message);
					}
				}

				continue;
			}

			$this->logger->logDebug('START: executeSource('.$source->getId().')');
			$t = microtime(true);
			$is_mem_limit = false;
			try {
				$this->executeSource($source);
			} catch (ProcessingException $e) {
				if ($e->getCode() == ProcessingException::MEMORY_LIMIT) {
					$is_mem_limit = true;
				} else {
					$this->logger->logError("Exception: " . $e->getMessage());
				}
			}

			$this->logger->logDebug(sprintf('FINISH: executeSource('.$source->getId().') - %.4fs', microtime(true)-$t));

			$m_end = memory_get_usage();
			$m_diff = $m_end - $m;

			$this->logger->log(sprintf("Memory usage: %.2f MB (total: %.2f MB)", $m_diff / 1024 / 1024, $m_end / 1024 / 1024), 'debug');

			$time_so_far = time() - $exec_start;
			if ($time_limit && $time_so_far >= $time_limit) {
				$this->logger->logInfo("Hit time limit, breaking");
				break;
			}

			if ($is_mem_limit) {
				$this->logger->logInfo("Hit memory limit, breaking");
				break;
			}

			$this->message_count++;

			if ($this->soft_time_limit) {
				$t = microtime(true) - DP_START_TIME;
				if ($t > $this->soft_time_limit) {
					$this->logger->logWarn(sprintf("Hit soft time limit, breaking :: Running for %.3fs", $t));
					break;
				}
			}
		}

		$fetcher->close();

		$end_time = microtime(true);
		$this->logger->log(sprintf(
			"Finished processing account. Took %.2f seconds. Peak memory %.2f MB (current %.2f MB).",
			$end_time - $start_time,
			memory_get_peak_usage() / 1024 / 1024,
			memory_get_usage() / 1024 / 1024
		), 'info');
	}


	/**
	 * @return array
	 */
	private function getFromHeaders()
	{
		if ($this->from_headers !== null) {
			return $this->from_headers;
		}

		$from_headers = explode(',', App::$container->getSetting('core_email.from_email_headers'));
		$from_headers = Arrays::func($from_headers, 'trim');
		$from_headers = Arrays::func($from_headers, 'strtolower');
		$from_headers = Arrays::removeFalsey($from_headers);

		if (!$from_headers) {
			$from_headers = array('from');
		}

		return $from_headers;
	}


	/**
	 * @param EmailAccount $account
	 * @return Fetcher\Exchange|Fetcher\Imap|Fetcher\Pop3
	 * @throws \InvalidArgumentException
	 */
	private function createFetcher(EmailAccount $account)
	{
		if (!$account->incoming_account) {
			throw new \InvalidArgumentException("No incoming email account");
		}

		switch ($account->incoming_account->getType()) {
			case 'pop3':
				return new Fetcher\Pop3($account, 20971520);
			case 'gmail':
				return new Fetcher\Pop3($account, 20971520);
			case 'imap':
				return new Fetcher\Imap($account, 20971520);
			case 'exchange':
				return new Fetcher\Exchange($account, 20971520);
			case 'noop':
			case 'null':
				return new Fetcher\Noop($account);
			default:
				throw new \InvalidArgumentException("Unknown incoming email account: {$account->incoming_account->getType()}");
		}
	}
}
