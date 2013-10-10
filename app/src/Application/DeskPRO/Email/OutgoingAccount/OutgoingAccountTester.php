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
	 * @var
	 */
	private $exception;

	/**
	 * @var bool
	 */
	private $is_success = false;

	/**
	 * @var \Swift_Plugins_Loggers_ArrayLogger
	 */
	private $swift_arraylogger;

	/**
	 * @var \Swift_Message
	 */
	private $swift_message;

	public function __construct(OutgoingAccountInterface $account)
	{
		$this->swift_arraylogger = new \Swift_Plugins_Loggers_ArrayLogger();
		$this->account = $account;
	}

	/**
	 * Run the test
	 *
	 * @return bool
	 */
	public function test($to_address, $from_address, $subject, $message)
	{
		$this->swift_message = \Swift_Message::newInstance()
			->setSubject($subject)
			->setBody($message)
			->setFrom($from_address)
			->setTo($to_address);

		try {
			if ($this->account instanceof SmtpAccount) {
				$this->_testSmtp($this->account);
			} else if ($this->account instanceof GmailAccount) {
				$this->_testGmail($this->account);
			} else if ($this->account instanceof PhpMailAccount) {
				$this->_testMail($this->account);
			}
		} catch (\Exception $e) {
			$this->swift_arraylogger->add("[error] " . $e->getMessage());
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
	 * @param \Swift_SmtpTransport $transport
	 */
	private function sendWithTransport(\Swift_Transport $transport)
	{
		$mailer = \Swift_Mailer::newInstance($transport);

		$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->swift_arraylogger));

		$failed = null;
		if (!$mailer->send($this->swift_message, $failed)) {
			$this->is_success = false;
		} else {
			$this->is_success = true;
		}
	}


	/**
	 * @param SmtpAccount $account
	 */
	private function _testSmtp(SmtpAccount $account)
	{
		$this->swift_arraylogger->add("Testing SmtpAccount");

		$this->swift_arraylogger->add("[options] host: {$account->host}");
		$this->swift_arraylogger->add("[options] port: {$account->port}");
		$this->swift_arraylogger->add("[options] secure: {$account->secure}");
		$this->swift_arraylogger->add("[options] username: {$account->username}");
		$this->swift_arraylogger->add("[options] password: {$account->password}");

		$transport = \Swift_SmtpTransport::newInstance(
			$account->host,
			$account->port,
			$account->secure
		);
		if ($account->username) {
			$transport->setUsername($account->username);
			$transport->setPassword($account->password);
		}

		$this->sendWithTransport($transport);
	}


	/**
	 * @param GmailAccount $account
	 */
	private function _testGmail(GmailAccount $account)
	{
		$this->swift_arraylogger->add("Testing GmailAccount");
		$smtp = new SmtpAccount();
		$smtp->setOptions(array(
			'username' => $account->username,
			'password' => $account->password,
			'host'     => 'smtp.gmail.com',
			'port'     => 465,
			'secure'   => 'ssl'
		));
		$this->_testSmtp($smtp);
	}


	/**
	 * @param PhpMailAccount $account
	 */
	public function _testMail(PhpMailAccount $account)
	{
		$this->swift_arraylogger->add("Testing PhpMailAccount");
		$this->swift_arraylogger->add("(No detailed logging is available using the PHP mail() transport.)");
		$transport = \Swift_MailTransport::newInstance();
		$this->sendWithTransport($transport);
	}


	/**
	 * @return string
	 */
	public function getLog()
	{
		return $this->swift_arraylogger->dump();
	}
}