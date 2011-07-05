<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Mail\Plugins;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Completely turns off email sending
 */
class DebugToFile implements \Swift_Events_SendListener
{
	protected $filepath;
	protected $cancel_send = false;

	public function __construct($filepath, $cancel_send = false)
	{
		$this->filepath = rtrim($filepath, "/\\");
		$this->cancel_send = $cancel_send;
	}

	public function sendPerformed(\Swift_Events_SendEvent $evt)
	{

	}

	public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
	{
		if ($this->cancel_send) {
			$evt->cancelBubble();
		}

		$message = $evt->getMessage();
		$name = time() . '_' . preg_replace('#[^a-zA-Z0-9]#', '-', $message->getSubject()) . '.txt';
		$name = preg_replace('#-{,2}#', '-', $name);

		$path = $this->filepath . DIRECTORY_SEPARATOR . $name;

		file_put_contents($path, $message->toString());
	}
}