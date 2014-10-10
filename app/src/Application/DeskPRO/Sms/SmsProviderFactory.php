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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\Sms;

use Orb\Sms\Provider\ClickatellSmsProvider;
use Orb\Sms\Provider\TwilioSmsProvider;

class SmsProviderFactory
{
	/**
	 * @param       $provider_name
	 * @param array $params
	 * @return \Orb\Sms\SmsProviderInterface
	 */
	public static function  create($provider_name, array $params)
	{
		switch ($provider_name) {
			case 'twilio':
				$sid        = $params['sid'];
				$auth_token = $params['auth_token'];
				return new TwilioSmsProvider($sid, $auth_token);
			case 'clickatell':
				$user     = $params['user'];
				$password = $params['password'];
				$api_id   = $params['api_id'];
				return new ClickatellSmsProvider($user, $password, $api_id);
		}

		throw new \InvalidArgumentException(
			"sms provider '$provider_name' does not exist, please check logic inside of SmsProviderFactory'"
		);
	}
}
