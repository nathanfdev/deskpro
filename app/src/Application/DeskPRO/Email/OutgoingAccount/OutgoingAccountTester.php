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

namespace Application\DeskPRO\Email\OutgoingAccount;

use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Orb\Util\Arrays;

class OutgoingAccountTester
{
	/**
	 * @var \Application\DeskPRO\Email\OutgoingAccount\OutgoingAccountInterface
	 */
	private $account;

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

	public function __construct(OutgoingAccountInterface $account)
	{
		$this->account = $account;

		$this->logger        = new Logger();
		$this->logger_writer = new ArrayWriter();
		$this->logger->addWriter($this->logger_writer);
	}

	/**
	 * Run the test
	 *
	 * @return bool
	 */
	public function test($to_address, $from_address, $subject, $message)
	{
		if ($this->account instanceof SmtpAccount) {
			$this->_testPop3($this->account, $to_address, $from_address, $subject, $message);
		} else if ($this->account) {
			$this->_testGmail($this->account, $to_address, $from_address, $subject, $message);
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
	 * @param SmtpAccount $account
	 */
	private function _testSmtp(SmtpAccount $account, $to_address, $from_address, $subject, $message)
	{
		$this->logger->logInfo('Testing SmtpAccount');
	}


	/**
	 * @param GmailAccount $account
	 */
	private function _testGmail(GmailAccount $account, $to_address, $from_address, $subject, $message)
	{
		$this->logger->logInfo('Testing GmailAccount');
		$smtp = new SmtpAccount();
		$smtp->setOptions(array(
			'username' => $account->username,
			'password' => $account->password,
			'host'     => 'smtp.gmail.com',
			'port'     => 465,
			'secure'   => 'ssl'
		));
		$this->_testSmtp($smtp, $to_address, $from_address, $subject, $message);
	}


	/**
	 * @return string
	 */
	public function getLog()
	{
		return $this->logger_writer->getMessagesAsString();
	}
}