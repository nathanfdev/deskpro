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
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\EmailGateway;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Strings;

/**
 * This runs collection and processsing in gateways
 */
class Runner
{
	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway[]
	 */
	protected $gateways;

	/**
	 * @var \Orb\Log\Writer\ArrayWriter
	 */
	protected $log_messages;

	/**
	 * When non-0, sets the PHP time limit per iteration
	 *
	 * @var int
	 */
	protected $set_time_limit = 0;

	public function __construct()
	{
		$this->logger = new \Application\DeskPRO\Log\Logger();
	}


	/**
	 * Set the PHP time limit for a single message. This uses set_time_limit()
	 * and resets it every iteration.
	 *
	 * This is used as an infinite-loop type preventative measure. PHP will halt
	 * the script, and whatever message that was being processed will be stuck in the 'inserted'
	 * state.
	 *
	 * @param $time_limit
	 */
	public function setPhpTimeLimit($time_limit)
	{
		$this->set_time_limit = $time_limit;
	}


	/**
	 * @param $logger \Application\DeskPRO\Log\Logger
	 */
	public function setLogger(\Application\DeskPRO\Log\Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * Set the gateways to process
	 *
	 * @param $gateways
	 */
	public function setGateways(array $gateways)
	{
		$this->gateways = $gateways;
	}


	/**
	 * Load gateways from the database
	 *
	 * @param bool $include_disabled True to also include disabled gateways
	 */
	public function loadGatewaysFromDb($include_disabled = false)
	{
		if ($include_disabled) {
			$this->gateways = App::getOrm()->getRepository('DeskPRO:EmailGateway')->findAll();
		} else {
			$this->gateways = App::getOrm()->getRepository('DeskPRO:EmailGateway')->getAllEnabled();
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

		if ($this->gateways) {
			foreach ($this->gateways as $gateway) {
				$this->executeGateway($gateway, $time_limit);

				$time_limit -= (time() - $exec_start);
				if ($time_limit <= 0) {
					break;
				}
			}
		}
	}


	/**
	 * Executes a single source. Good for re-processing.
	 *
	 * @param \Application\DeskPRO\Entity\EmailSource $source
	 * @throws \Exception
	 */
	public function executeSource(EmailSource $source)
	{
		$gateway = $source->gateway;

		$this->logger->log("Start processing source {$source['id']}", 'info');
		$start_time = microtime(true);

		// If its an error we cant process (eg message too big we dont have the whole email),
		// quit out now
		if ($source->status == 'error' && $source->error_code == 'message_too_big') {
			$this->logger->log(sprintf("Source marked as error :: %s", $source->error_code), 'debug');
			return;
		}

		$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
		$reader->setRawSource($source['raw_source']);
		$reader->setProperty('email_source', $source);

		$to = array();
		foreach ($reader->getToAddresses() as $x) {
			$to[] = $x->getEmail();
		}
		$to = implode(', ', $to);

		$from = $reader->getFromAddress()->getEmail();

		$subj = substr($reader->getSubject()->getSubject(), 0, 40);
		$this->logger->log("[Message] To: $to :: From: $from :: Subject: $subj", 'debug');

		App::getOrm()->beginTransaction();

		try {

			if (!$this->log_messages) {
				$this->log_messages = new \Orb\Log\Writer\ArrayWriter();
				$this->logger->addWriter($this->log_messages);
			}

			$this->log_messages->clear();

			$pre_processor = new PreProcessor($gateway, $reader, array('logger' => $this->logger));
			$pre_processor->run();

			$created_obj = null;
			if ($pre_processor->isValid()) {

				try {
					$proc = $gateway->getNewProcessor($reader, array('logger' => $this->logger, 'logger_messages' => $this->log_messages));
					$created_obj = $proc->run();

					if ($proc->isValid()) {
						$source['status'] = 'complete';
					} else {
						$source['status'] = 'error';
						$source['error_code'] = $proc->getErrorCode();
					}

					$source['source_info'] = $proc->getSourceInfo();
				} catch (\Exception $e) {

					$e->_dp_sn = KernelErrorHandler::genSessionName();

					$errinfo = KernelErrorHandler::getExceptionInfo($e);
					KernelErrorHandler::logErrorInfo($errinfo);

					$source['status'] = 'error';
					$source['error_code'] = EmailSource::ERR_SERVER_ERROR;
					$source['source_info'] = $errinfo;
				}
			} else {
				$source['status'] = 'error';
				$source['error_code'] = $pre_processor->getErrorCode();
				$source['source_info'] = $pre_processor->getSourceInfo();
			}

			if ($created_obj) {
				$source['object_type'] = strtolower(\Orb\Util\Util::getBaseClassname($created_obj));
				$source['object_id'] = $created_obj->id;

				$this->logger->log("Created " . get_class($created_obj) . ": " . $created_obj->getId(), 'debug');
			}
		} catch (\Exception $e) {
			App::getOrm()->rollback();

			$this->_updateSource($source);

			throw $e;
		}

		$this->_updateSource($source);
		App::getOrm()->commit();

		$end_time = microtime(true);
		$this->logger->log(sprintf("Finished processing source. Took %.2f seconds.", $end_time - $start_time), 'info');
	}

	/**
	 * Execute a gateway
	 *
	 * $time_limit is the max time before the while loop breaks. The method will usually continue to process mail
	 * until there is no email left. If you specify a time limit then the process will break after $time_limit seconds.
	 * Note this check is done after processing of a message, it does not abort. This means that it's possible the time
	 * limit will be exceeded (e.g., time limit of 10, message starts processing at 9 seconds so it continues).
	 *
	 * @param \Application\DeskPRO\EntityRepository\EmailGateway $gateway
	 * @param int $time_limit The max time spent processing email before we break.
	 * @throws \Exception
	 */
	public function executeGateway(EmailGateway $gateway, $time_limit = 0)
	{
		$this->logger->log("Start processing {$gateway['title']} {$gateway['gateway_type']}:{$gateway['connection_type']}", 'info');
		$start_time = microtime(true);

		/** @var $fetcher \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher */
		$fetcher = $gateway->getFetcher();
		$fetcher->setLogger($this->logger);
		$fetcher->setMaxSize(App::getSetting('core.gateway_max_email'));

		$exec_start = time();

		while (true) {

			try {
				$source = $fetcher->readNext();
				if (!$source) {
					break;
				}
			} catch (\Exception $e) {
				$einfo = KernelErrorHandler::getExceptionInfo($e);
				KernelErrorHandler::logErrorInfo($einfo);
				break;
			}

			if (!$this->log_messages) {
				$this->log_messages = new \Orb\Log\Writer\ArrayWriter();
				$this->logger->addWriter($this->log_messages);
			}

			$this->log_messages->clear();

			if ($this->set_time_limit) {
				@set_time_limit($this->set_time_limit);
			}

			$this->logger->log("[Gateway {$gateway['id']}] Read source ID {$source['id']}", 'debug');

			// Already marked as an error (e.g., message too big) so we dont
			// process it through the gateway handlers
			if ($source->status == 'error') {
				$this->logger->log(sprintf("Source marked as error :: %s", $source->error_code), 'debug');
				continue;
			}

			$reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
			$reader->setRawSource($source['raw_source']);
			$reader->setProperty('email_source', $source);

			$to = array();
			foreach ($reader->getToAddresses() as $x) {
				$to[] = $x->getEmail();
			}
			$to = implode(', ', $to);

			$from = $reader->getFromAddress()->getEmail();

			$subj = substr($reader->getSubject()->getSubject(), 0, 40);
			$this->logger->log("[Message] To: $to :: From: $from :: Subject: $subj", 'debug');

			App::getOrm()->beginTransaction();

			try {

				$pre_processor = new PreProcessor($gateway, $reader, array('logger' => $this->logger));
				$pre_processor->run();

				$created_obj = null;
				if ($pre_processor->isValid()) {

					try {
						$proc = $gateway->getNewProcessor($reader, array('logger' => $this->logger, 'logger_messages' => $this->log_messages));
						$created_obj = $proc->run();

						if ($proc->isValid()) {
							$source['status'] = 'complete';
						} else {
							$source['status'] = 'error';
							$source['error_code'] = $proc->getErrorCode();
						}

						$source['source_info'] = $proc->getSourceInfo();

						App::getOrm()->commit();

					} catch (\Exception $e) {

						App::getOrm()->rollback();

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
					$source['status'] = 'error';
					$source['error_code'] = $pre_processor->getErrorCode();
					$source['source_info'] = $pre_processor->getSourceInfo();
				}

				if ($created_obj) {
					$source['object_type'] = strtolower(\Orb\Util\Util::getBaseClassname($created_obj));
					$source['object_id'] = $created_obj->id;
				}

				if ($created_obj) {
					$this->logger->log("Created " . get_class($created_obj) . ": " . $created_obj->getId(), 'debug');
				}
			} catch (\Exception $e) {
				App::getOrm()->rollback();

				$this->_updateSource($source);

				throw $e;
			}

			$this->_updateSource($source);
			$this->log_messages->clear();

			$time_so_far = time() - $exec_start;
			if ($time_limit && $time_so_far >= $time_limit) {
				break;
			}
		}

		$fetcher->close();

		$end_time = microtime(true);
		$this->logger->log(sprintf("Finished processing gateway. Took %.2f seconds.", $end_time - $start_time), 'info');
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
		App::getDb()->update('email_sources', array(
			'status'      => $source['status'],
			'error_code'  => $source['error_code'],
			'source_info' => serialize($source['source_info'] ?: array()),
		), array('id' => $source->getId()));
	}
}
