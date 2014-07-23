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
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\EmailGateway\Fetcher;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
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

		$this->accounts = array_filter($this->accounts, function($a) { return !($a->incoming_account instanceof NoopConfig); });
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
	 * Executes a single source. Good for re-processing.
	 *
	 * @param \Application\DeskPRO\Entity\EmailSource $source
	 * @param AbstractReader $reader
	 * @throws \Exception
	 * @return string Status code
	 */
	public function executeSource(EmailSource $source, AbstractReader $reader = null)
	{
		if (!$this->log_messages) {
			$this->log_messages = new \Orb\Log\Writer\ArrayWriter();
			$this->logger->addWriter($this->log_messages);
		}

		$this->logger->logDebug('Executing Source ' . $source->getId());

		$account = $source->email_account;

		// Attempt to detect if we should break due to memory
		$mem = memory_get_usage();
		$avail = deskpro_install_check_parseinisize(@ini_get('memory_limit'));
		if ($mem && $mem > 0 && $avail && $avail > 0) {
			$remain = $avail - $mem;
			$min = max(10485760, $source->blob->filesize * 4);
			$room = $remain - $min;

			$str = sprintf("Memory Used: %d    Memory Max: %d    Est Memory Required: %d    Est Memory After: %d", $mem, $avail, $min, $room);
			$this->logger->log($str, 'debug');

			if ($remain < $min) {
				$this->logger->log(sprintf("Detected that we are at the memory limit, quitting run"), 'debug');

				$e = new \Exception("Detected at memory limit: $str");
				KernelErrorHandler::logException($e, true);

				return 'memory_limit';
			}
		}

		// Mark as processing now
		$this->logger->logDebug('Marking source as processing');
		$source->status = 'processing';
		App::getOrm()->persist($source);
		App::getOrm()->flush();

		if (!$reader) {
			try {
				$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
				$reader->setRawSource($source['raw_source']);
			} catch (\Exception $e) {
				$this->logger->log(sprintf("Could not set source: %s", $e->getMessage()), 'info');

				$e->_dp_sn = KernelErrorHandler::genSessionName();
				$errinfo = KernelErrorHandler::getExceptionInfo($e);
				KernelErrorHandler::logErrorInfo($errinfo);

				$source['status'] = 'error';
				$source['error_code'] = EmailSource::ERR_SERVER_ERROR;
				$source['source_info'] = $errinfo;

				$this->_updateSource($source);
				if ($this->log_messages) {
					$this->log_messages->clear();
				}
				$source->clearRawSource();
				App::getOrm()->detach($source);
				$source = null;

				if ($reader) {
					$reader->_kill();
					$reader = null;
				}

				gc_collect_cycles();

				return 'decode_error';
			}
		}

		if (!$reader->hasProperty('email_source')) {
			$reader->setProperty('email_source', $source);
		}

		$to = array();
		foreach ($reader->getToAddresses() as $x) {
			$to[] = $x->getEmail();
		}
		$to = implode(', ', $to);

		$from = $reader->getFromAddress()->getEmail();

		$subj = substr($reader->getSubject()->getSubject(), 0, 40);
		$this->logger->log("[Message] To: $to :: From: $from :: Subject: $subj", 'debug');

		$from_headers = $this->getFromHeaders();
		if ($from_headers) {
			$this->logger->logDebug(sprintf("From header priority: %s", implode(', ', $from_headers)));
			$reader->setFromHeaderPriority($from_headers);
			$from = $reader->getFromAddress()->getEmail();
			$this->logger->logDebug(sprintf("[Message] Using From: %s", $from));
		}

		App::getOrm()->beginTransaction();

		try {

			$pre_processor = new PreProcessor($account, $reader, array('logger' => $this->logger));
			$pre_processor->run();

			$created_obj = null;
			if ($pre_processor->isValid()) {
				$pre_processor = null;

				$this->logger->log("Preprocessor complete", 'info');

				try {
					$proc = $this->account_manager->getEmailProcessor($account, $reader, array('logger' => $this->logger, 'logger_messages' => $this->log_messages));
					if (!$proc) {
						$this->logger->log("No email processor for account", 'warn');
						$source['status'] = 'rejected';
						$source['error_code'] = 'invalid_address';
					} else {
						$created_obj = $proc->run();

						if ($proc->isValid()) {
							$this->logger->log("Processor complete", 'info');
							$source['status'] = 'complete';
							$source['error_code'] = null;
						} else {
							$source['status'] = $proc->getErrorType() == 'rejected' ? 'rejected' : 'error';
							$source['error_code'] = $proc->getErrorCode();
							$this->logger->log(sprintf("Processor error: %s", $source['error_code']), 'info');
						}

						$source_info = $proc->getSourceInfo();
						if ($source_info) {
							$source_info = implode("\n", $source_info);
							try {
								$blob = App::$container->getBlobStorage()->createBlobRecordFromString($source_info, 'email-process.log', 'plain/text');
								$source['log_blob'] = $blob;
							} catch (\Exception $e) {}
						}
					}

					$proc = null;

					App::getOrm()->commit();

				} catch (\Exception $e) {

					$this->logger->log(sprintf("Processor exception: %s", $e->getMessage()), 'info');

					KernelErrorHandler::logException($e);

					if (App::getDb()->isTransactionActive()) {
						App::getDb()->rollback();
					}

					$e->_dp_sn = KernelErrorHandler::genSessionName();

					$errinfo = KernelErrorHandler::getExceptionInfo($e);
					KernelErrorHandler::logErrorInfo($errinfo);

					$source['status'] = 'error';
					$source['error_code'] = EmailSource::ERR_SERVER_ERROR;

					foreach ($errinfo as &$_v) {
						if (is_object($_v)) {
							$_v = get_class($_v);
						} elseif (is_array($_v)) {
							$_v = KernelErrorHandler::varToString($_v);
						}
					}
					$source['source_info'] = $errinfo;
				}
			} else {
				$source['status'] = $pre_processor->getErrorType() ?: 'error';
				$source['error_code'] = $pre_processor->getErrorCode();

				$source_info = $pre_processor->getSourceInfo();
				if ($source_info) {
					$source_info = implode("\n", $source_info);
					try {
						$blob = App::$container->getBlobStorage()->createBlobRecordFromString($source_info, 'email-process.log', 'plain/text');
						$source['log_blob'] = $blob;
					} catch (\Exception $e) {}
				}

				$pre_processor = null;

				$this->logger->log(sprintf("Preprocessor error: %s", $source['error_code']), 'info');

				App::getOrm()->commit();
			}

			if ($created_obj) {
				$source['object_type'] = strtolower(\Orb\Util\Util::getBaseClassname($created_obj));
				$source['object_id'] = $created_obj->id;

				$this->logger->log("Created " . get_class($created_obj) . ": " . $created_obj->getId(), 'debug');
			}
		} catch (\Exception $e) {

			$this->logger->log(sprintf("Preprocessor exception: %s", $e->getMessage()), 'info');

			if (App::getDb()->isTransactionActive()) {
				App::getDb()->rollback();
			}

			$this->_updateSource($source);

			throw $e;
		}

		$this->_updateSource($source);
		if ($this->log_messages) {
			$this->log_messages->clear();
		}
		$source->clearRawSource();

		$created_obj = null;

		App::getOrm()->detach($source);
		$source = null;

		if ($reader) {
			$reader->_kill();
			$reader = null;
		}

		gc_collect_cycles();

		return 'okay';
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
			WHERE status = 'inserted' AND email_account_id = ?
			ORDER BY id ASC
		", array($account->getId()));

		$this->logger->logDebug(sprintf("%d inserted messages being processed first", count($inserted_source_ids)));

		while (true) {
			// Make sure any records are flusehd
			App::getOrm()->flush();

			// Protection against nested transactions.
			// This should not be needed, but its a safety against unclosed transactions.
			// Without it, a mistake somewhere down the line can result in an entire
			// process of emails being rolledback.
			if (App::getDb()->isTransactionActive()) {
				$this->logger->log("WARNING: Unclosed transaction!", 'info');
				$e = new \RuntimeException("WARNING: Unclosed transaction!");
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
					$einfo = KernelErrorHandler::getExceptionInfo($e);
					KernelErrorHandler::logErrorInfo($einfo);
					break;
				}
			}

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
			$ret_code = $this->executeSource($source);
			$this->logger->logDebug(sprintf('FINISH: executeSource('.$source->getId().') - %.4fs', microtime(true)-$t));

			$m_end = memory_get_usage();
			$m_diff = $m_end - $m;

			$this->logger->log(sprintf("Memory usage: %.2f MB (total: %.2f MB)", $m_diff / 1024 / 1024, $m_end / 1024 / 1024), 'debug');

			$time_so_far = time() - $exec_start;
			if ($time_limit && $time_so_far >= $time_limit) {
				break;
			}

			if ($ret_code == 'memory_limit') {
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
	 * Updating the source without Doctrine to ensure the record is still updated
	 * when there is a critical error during a commit in the UoW. Since Doctrine
	 * cannot recover from a critical error during commit-time, if we'd try to persist
	 * the entity through the EM we'd get an error about the entity manager being closed.
	 *
	 * Examples of when this might happen would be invalid forign keys, database connection
	 * error that happened precisely within the time it took to do the commit, or any other
	 * error in that time.
	 *
	 * @param $source
	 */
	protected function _updateSource($source)
	{
		$this->logger->log(sprintf("Updating source (status: %s %s)", $source['status'], $source['error_code']), 'info');

		App::getDb()->update('email_sources', array(
			'status'      => $source['status'],
			'error_code'  => $source['error_code'],
			'source_info' => serialize($source['source_info'] ?: array()),
			'log_blob_id' => $source['log_blob'] ? $source['log_blob']->getId() : null
		), array('id' => $source->getId()));
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
			default:
				throw new \InvalidArgumentException("Unknown incoming email account: {$account->incoming_account->getType()}");
		}
	}
}
