<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Mail
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Mail;

use Application\DeskPRO\App;

use Orb\Mail\Message;
use Orb\Util\Strings;
use Orb\Util\Util;

require_once(DP_ROOT . '/vendor/swiftmailer/lib/swift_required.php');

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class Mailer extends \Swift_Mailer
{
	public function __construct(\Swift_Transport $transport)
	{
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

		$this->registerPlugin(new \Orb\Mail\Plugins\DefaultFromAddress(App::getConfig('mail.default_from')));
	}

	public static function newInstance(\Swift_Transport $transport)
	{
		return new self($transport);
	}

	/**
	 * @return \Orb\Mail\Message
	 */
	public function createMessage($service = 'message')
	{
		if ($service == 'message') {
			$message = \Orb\Mail\Message::newInstance();
			return $message;
		}

		return parent::createMessage($service);
	}
}
