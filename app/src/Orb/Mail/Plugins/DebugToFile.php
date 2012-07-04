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
 * Orb
 *
 * @package Orb
 * @subpackage Mail
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
		$name = time() . mt_rand(1000,9999) . '_' . preg_replace('#[^a-zA-Z0-9]#', '-', substr($message->getSubject(), 0, 50)) . '.txt';
		$name = preg_replace('#-{,2}#', '-', $name);

		$path = $this->filepath . DIRECTORY_SEPARATOR . $name;

		file_put_contents($path, $message->toString());
	}
}
