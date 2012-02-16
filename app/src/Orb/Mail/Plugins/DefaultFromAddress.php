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
 * If no 'from' is set on a message, this will give it a default
 */
class DefaultFromAddress implements \Swift_Events_SendListener
{
	protected $from;

	public function __construct($from)
	{
		$this->from = $from;
	}

	public function sendPerformed(\Swift_Events_SendEvent $evt)
	{

	}

	public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
	{
		$message = $evt->getMessage();
		$headers = $message->getHeaders();

		if (!$message->getFrom()) {
			$message->setFrom($this->from);
		}
	}
}