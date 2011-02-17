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

use \Application\DeskPRO\App;

use \Orb\Mail\Message;
use \Orb\Util\Strings;
use \Orb\Util\Util;

/**
 * This transport takes care of initializing any other transports based on settings
 * etc, and also queuing.
 */
class Mailer extends \Swift_Mailer
{
	public function __construct(\Swift_Transport $transport)
	{
		parent::__construct($transport);

		if (App::getConfig('debug_mail_force_to')) {
			$this->registerPlugin(new \Orb\Mail\Plugins\ForceToAddress(App::getConfig('debug_mail_force_to')));
		}
		if (App::getConfig('debug_mail_disable_send')) {
			$this->registerPlugin(new \Orb\Mail\Plugins\CancelSend());
		}
		if (App::getConfig('debug_mail_debug_to_file')) {
			$filepath = App::getConfig('debug_mail_debug_to_file');
			if ($filepath === true) {
				$filepath = '%log_dir%/emails';
			}

			$filepath = str_replace('%log_dir%', App::getLogDir(), $filepath);
			if (!is_dir($filepath)) {
				@mkdir($filepath, 0777);
			}

			$this->registerPlugin(new \Orb\Mail\Plugins\DebugToFile($filepath));
		}

		$this->registerPlugin(new \Orb\Mail\Plugins\DefaultFromAddress(App::getConfig('mail.default_from')));
	}
}