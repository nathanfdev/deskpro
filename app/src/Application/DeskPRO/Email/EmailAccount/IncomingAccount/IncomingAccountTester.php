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
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;

class IncomingAccountTester
{
	/**
	 * @var \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
	 */
	private $account_config;

	/**
	 * @var \Orb\Log\Logger
	 */
	private $logger;

	/**
	 * @var \Orb\Log\Writer\ArrayWriter
	 */
	private $logger_writer;

	/**
	 * @var
	 */
	private $exception;

	/**
	 * @var bool
	 */
	private $is_success = false;

	/**
	 * @var array
	 */
	private $message_count = 0;

	public function __construct(AccountConfigInterface $account_config)
	{
		$this->account_config = $account_config;

		$this->logger        = new Logger();
		$this->logger_writer = new ArrayWriter();
		$this->logger->addWriter($this->logger_writer);
	}


	/**
	 * Run the test
	 *
	 * @return bool
	 */
	public function test()
	{
		switch ($this->account_config->getType()) {
			case 'pop3':
				$this->_testPop3();
				break;

			case 'imap':
				$this->_testImap();
				break;

			case 'exchange':
				$this->_testExchange();
				break;

			case 'gmail':
				$this->_testGmail();
				break;
		}

		return $this->is_success;
	}


	/**
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->is_success;
	}


	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}


	/**
	 * As part of the test, we fetch the count of messages.
	 *
	 * @return array|int
	 */
	public function getMessageCount()
	{
		return $this->message_count;
	}


	/**
	 * Tests Pop3
	 */
	private function _testPop3()
	{
		/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config $account_config */
		$account_config = $this->account_config;

		$this->logger->logInfo('Testing Pop3Account');

		try {
			$storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3(array(
				'host'     => $account_config->host,
				'user'     => $account_config->user,
				'password' => $account_config->password,
				'port'     => $account_config->port,
				'ssl'      => $account_config->secure_mode,
				'logger'   => $this->logger,
				'test_mode' => true,
			));

			$this->message_count = $storage->countMessages();

			$this->is_success = true;
		} catch (\Exception $e) {
			$this->logger->logError(sprintf("Error: %s", $e->getMessage()));
			$this->logger->logError(sprintf("(Code: %s:%s)", get_class($e), $e->getCode()));
			$this->logger->logError(KernelErrorHandler::formatBacktrace($e->getTrace()));
			$this->is_success = false;
		}
	}


	private function _testImap()
	{
		/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig $account_config */
		$account_config = $this->account_config;

		$this->logger->logInfo('Testing ImapAccount');

		try {
			$storage = new \Application\DeskPRO\EmailGateway\Storage\Imap(array(
				'host'     => $account_config->host,
				'user'     => $account_config->user,
				'password' => $account_config->password,
				'port'     => $account_config->port,
				'ssl'      => $account_config->secure_mode,
				'logger'   => $this->logger,
				'test_mode' => true,
			));
			if ($account_config->read_mailbox) {
				$storage->ensureMailboxExists($account_config->read_mailbox);
				$storage->setMailBox($account_config->read_mailbox);
			}

			if ($account_config->mode == 'read') {
				$ids = $storage->getAllUnseenMessageUids();
			} else {
				$ids = $storage->getAllMessageUids();
			}

			$this->logger->logInfo("Read IDs: " . implode(', ', $ids));
			$this->message_count = count($ids);

			$this->is_success = true;
		} catch (\Exception $e) {
			$this->logger->logError(sprintf("Error: %s", $e->getMessage()));
			$this->logger->logError(sprintf("(Code: %s:%s)", get_class($e), $e->getCode()));
			$this->logger->logError(KernelErrorHandler::formatBacktrace($e->getTrace()));
			$this->is_success = false;
		}
	}


	private function _testExchange()
	{
		/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig $account_config */
		$account_config = $this->account_config;

		$this->logger->logInfo('Testing ExchangeAccount');

		try {
			$storage = new \Application\DeskPRO\EmailGateway\Storage\Exchange(array(
				'host'     => $account_config->host,
				'user'     => $account_config->user,
				'password' => $account_config->password,
				'port'     => $account_config->port,
				'logger'   => $this->logger,
				'test_mode' => true,
			));
			if ($account_config->read_mailbox) {
				$storage->ensureFolderExists($account_config->read_mailbox);
			}

			$unread_only = false;
			$folder = null;

			if ($account_config->mode == 'read') {
				$unread_only = true;
			}
			if ($account_config->read_mailbox) {
				$folder = $account_config->read_mailbox;
			}

			$ids = $storage->searchIds(100, $unread_only, $folder);

			$this->logger->logInfo("Read IDs: " . implode(', ', $ids));
			$this->message_count = count($ids);

			$this->is_success = true;
		} catch (\Exception $e) {
			$this->logger->logError(sprintf("Error: %s", $e->getMessage()));
			$this->logger->logError(sprintf("(Code: %s:%s)", get_class($e), $e->getCode()));
			$this->logger->logError(KernelErrorHandler::formatBacktrace($e->getTrace()));
			$this->is_success = false;
		}
	}


	/**
	 * Tests Gmail
	 */
	private function _testGmail()
	{
		/** @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig $account_config */
		$account_config = $this->account_config;

		$this->logger->logInfo('Testing GmailAccount');

		try {
			$storage = new \Application\DeskPRO\EmailGateway\Storage\Pop3(array(
				'host'     => 'pop.gmail.com',
				'user'     => $account_config->user,
				'password' => $account_config->password,
				'port'     => 995,
				'ssl'      => 'ssl',
				'logger'   => $this->logger,
				'test_mode' => true,
			));

			$this->message_count = $storage->countMessages();

			$this->is_success = true;
		} catch (\Exception $e) {
			$this->exception = $e;
			$this->logger->logError(sprintf("Error: %s", $e->getMessage()));
			$this->logger->logError(sprintf("(Code: %s:%s)", get_class($e), $e->getCode()));
			$this->logger->logError(KernelErrorHandler::formatBacktrace($e->getTrace()));
			$this->is_success = false;
		}
	}


	/**
	 * @return string
	 */
	public function getLog()
	{
		return $this->logger_writer->getMessagesAsString();
	}
}