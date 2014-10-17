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
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 */

namespace Orb\Mail\Plugins;

use Orb\Log\Logger;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * If no 'from' is set on a message, this will give it a default
 */
class DefaultFromAddress implements \Swift_Events_SendListener
{
	protected $from;
	protected $name = '';
	protected $logger;

	public function __construct($from, $name = '', Logger $logger = null)
	{
		$this->from = $from;
		$this->name = $name;
		$this->logger = $logger;

		if ($this->logger) $this->logger->logInfo(sprintf("[DefaultFromAddress] Default from: %s <%s>", $name, $from));
	}

	public function sendPerformed(\Swift_Events_SendEvent $evt)
	{

	}

	public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
	{
		$message = $evt->getMessage();

		if ($this->logger) $this->logger->logInfo(sprintf("[DefaultFromAddress] Checking: %s", print_r($message->getFrom(), true)));

		if (!$message->getFrom()) {
			if ($this->logger) $this->logger->logInfo("[DefaultFromAddress] Setting name and email");
			$message->setFrom($this->from, $this->name);
		} else {
			$from = $message->getFrom();

			if (is_array($from)) {
				foreach ($from as $k => &$v) {
					if (!$v) {
						if ($this->logger) $this->logger->logInfo("[DefaultFromAddress] Setting name");
						$v = $this->name;
					}
					break;
				}

				$message->setFrom($from);

			} elseif (is_string($from)) {
				// From is a string now,
				// It's either a string of email@example.com or Name <email@example.com>
				// So if its just an email, we want to prepend the default name
				if (\Orb\Validator\StringEmail::isValueValid($from)) {
					if ($this->logger) $this->logger->logInfo("[DefaultFromAddress] Setting name");
					$from = array($from	 => $this->name);
				}

				$message->setFrom($from);
			}
		}
	}
}
