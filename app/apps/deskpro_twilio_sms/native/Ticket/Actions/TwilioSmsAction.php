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
 * @category Twilio
 */

namespace deskpro_twilio_sms\Ticket\Actions;

use Application\DeskPRO\Tickets\Actions\AbstractSmsAction;
use Orb\Sms\Provider\TwilioSmsProvider;

class TwilioSmsAction extends AbstractSmsAction
{
	/**
	 * All children of this class need to construct their own provider from their config
	 *
	 * @return \Orb\Sms\SmsProviderInterface
	 */
	public function getSmsProvider()
	{
		$sid = $this->getApp()->getSetting('account_sid');
		$token = $this->getApp()->getSetting('auth_token');

		return new TwilioSmsProvider($sid, $token);
	}

	/**
	 * If your provider needs a "from" address to work, return a string. Otherwise, it's ok to return null.
	 *
	 * @return string|null
	 */
	public function getFromPhoneNumber()
	{
		return $this->getActionOption('from_number');
	}
}
