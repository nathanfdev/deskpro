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
 * @category Mail
 */

namespace Application\DeskPRO\Mail;

use Application\DeskPRO\App;

use Orb\Log\Logger;
use Orb\Log\Loggable;
use Orb\Util\Strings;
use Orb\Util\Util;

require_once(DP_ROOT . '/vendor/swiftmailer/lib/swift_required.php');

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class Mailer extends \Swift_Mailer implements Loggable
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
	 */
	protected $templating;

	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @var array
	 */
	protected $queued = array();

	protected $messagesLog = 0;

	public function __construct(\Swift_Transport $transport, \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating, Logger $logger = null)
	{
		if (!is_dir(dp_get_tmp_dir() . '/swiftmailer-cache')) {
			mkdir(dp_get_tmp_dir() . '/swiftmailer-cache', 0777, true);
		}

		\Swift_Preferences::getInstance()->setTempDir(dp_get_tmp_dir() . '/swiftmailer-cache');

		$this->messagesLog = new \Orb\Log\Writer\ArrayWriter();

		if ($logger) {
			$this->setLogger($logger);
		}

		$this->templating = $templating;

		$this->getLogger()->logInfo(sprintf("Setting transport: %s", get_class($transport)));
		if ($transport instanceof \Orb\Log\Loggable) {
			$transport->setLogger($this->getLogger());
		}

		parent::__construct($transport);

		if (App::getConfig('debug.mail.force_to')) {
			$this->getLogger()->logInfo(sprintf("debug.mail.force_to on: %s", App::getConfig('debug.mail.force_to')));
			$this->registerPlugin(new \Orb\Mail\Plugins\ForceToAddress(App::getConfig('debug.mail.force_to')));
		}

		if (App::getConfig('debug.mail.save_to_file')) {
			$filepath = App::getConfig('debug.mail.save_to_file');
			if ($filepath === true || is_numeric($filepath)) {
				$filepath = '%log_dir%/emails';
			}

			$filepath = str_replace('%log_dir%', App::getLogDir(), $filepath);
			if (!is_dir($filepath)) {
				@mkdir($filepath, 0777);
			}

			$this->getLogger()->logInfo(sprintf("debug.mail.save_to_file on: %s", $filepath));

			$this->registerPlugin(new \Orb\Mail\Plugins\DebugToFile($filepath, App::getConfig('debug.mail.disable_send', false)));

		} else if (App::getConfig('debug.mail.disable_send')) {
			// As an elseif becaue the DebugToFile can also disable send
			// If CancelSend is registered first, then the DebugToFile wont fire either
			// and we'll just have nothing

			$this->getLogger()->logInfo("debug.mail.disable_send");

			$this->registerPlugin(new \Orb\Mail\Plugins\CancelSend());
		}

		try {
			$default = App::getSetting('core.default_from_email');
			$name    = App::getSetting('core.deskpro_name');

			if (!$default) {
				if (!empty($_SERVER['HOST_NAME'])) {
					$default = 'deskpro@' . $_SERVER['HOST_NAME'];
				} elseif (@php_uname('n')) {
					$default = 'deskpro@' . php_uname('n');
				} else {
					$default = 'deskpro@localhost';
				}
			}

			$this->getLogger()->logInfo(sprintf("Default from: %s <%s>", $name, $default));

			if ($default) {
				$this->registerPlugin(new \Orb\Mail\Plugins\DefaultFromAddress($default, $name));
			}
		} catch (\Exception $e) {}

		\DpShutdown::add(array($this, 'sendQueuedSilent'), null, 'db_done_trans');
		\DpShutdown::add(array($this, 'sendQueuedSilent'), null, 'shutdown', 1000);
	}

	/**
	 * @return array
	 */
	public function getLogMessages()
	{
		return $this->messagesLog->getMessages();
	}

	/**
	 * Get the logger
	 *
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		if (!$this->logger) {
			$this->logger = new Logger();
		}

		return $this->logger;
	}


	/**
	 * Set the logger used
	 *
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
		$this->logger->addWriter($this->messagesLog);
	}


	/**
	 * @static
	 * @param \Swift_Transport $transport
	 * @return \Application\DeskPRO\Mail\Mailer
	 */
	public static function newInstance(\Swift_Transport $transport)
	{
		$templating = App::get('templating');
		$inst = self($transport, $templating);
		$inst->setLogger(App::getSystemService('mail_logger'));

		return $inst;
	}


	/**
	 * @return \Application\DeskPRO\Mail\Message
	 */
	public function createMessage($service = 'message')
	{
		if ($service == 'message') {
			$message = \Application\DeskPRO\Mail\Message::newInstance();
			$message->setEncoder(\Swift_Encoding::get8BitEncoding());
			$message->setTemplateEngine($this->templating);
			return $message;
		}

		return parent::createMessage($service);
	}

	public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$this->queued[] = $message;
	}

	public function sendQueued()
	{
		while ($message = array_shift($this->queued)) {
			$this->sendNow($message);
		}
	}

	public function sendQueuedSilent()
	{
		while ($message = array_shift($this->queued)) {
			try {
				$this->sendNow($message);
			} catch (\Exception $e) {
				$einfo = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
				\DeskPRO\Kernel\KernelErrorHandler::logErrorInfo($einfo);
			}
		}
	}

	public function sendNow(\Swift_Mime_Message $message, &$failedRecipients = null)
	{
		return parent::send($message, $failedRecipients);
	}
}
