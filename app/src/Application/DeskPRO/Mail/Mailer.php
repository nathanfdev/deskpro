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

use Orb\Util\Strings;
use Orb\Util\Util;

require_once(DP_ROOT . '/vendor/swiftmailer/lib/swift_required.php');

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class Mailer extends \Swift_Mailer
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
	 */
	protected $templating;

	public function __construct(\Swift_Transport $transport, \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating)
	{
		$this->templating = $templating;

		parent::__construct($transport);

		if (App::getConfig('debug.mail.force_to')) {
			$this->registerPlugin(new \Orb\Mail\Plugins\ForceToAddress(App::getConfig('debug.mail.force_to')));
		}

		if (App::getConfig('debug.mail.save_to_file')) {
			$filepath = App::getConfig('debug.mail.save_to_file');
			if ($filepath === true) {
				$filepath = '%log_dir%/emails';
			}

			$filepath = str_replace('%log_dir%', App::getLogDir(), $filepath);
			if (!is_dir($filepath)) {
				@mkdir($filepath, 0777);
			}

			$this->registerPlugin(new \Orb\Mail\Plugins\DebugToFile($filepath, App::getConfig('debug.mail.disable_send', false)));

		} else if (App::getConfig('debug.mail.disable_send')) {
			// As an elseif becaue the DebugToFile can also disable send
			// If CancelSend is registered first, then the DebugToFile wont fire either
			// and we'll just have nothing

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

			if ($default) {
				$this->registerPlugin(new \Orb\Mail\Plugins\DefaultFromAddress($default, $name));
			}
		} catch (\Exception $e) {}
	}

	public static function newInstance(\Swift_Transport $transport)
	{
		$templating = App::get('templating');
		return new self($transport, $templating);
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
}
