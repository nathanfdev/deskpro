<?php

namespace Orb\Sms\Provider;

use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;

class TwilioSmsProvider implements SmsProviderInterface
{

	/**
	 * @param string $fromPhoneNumber phone number to send to, provider should be able to handle any format
	 * @param string $toPhoneNumber   phone number, provider should be able to handle any format
	 * @param string $textMessage     the message to be sent to the given number
	 *
	 * @throws \Orb\Sms\SmsException
	 * @return \Orb\Sms\SmsResult
	 */
	public function sendMessage($fromPhoneNumber, $toPhoneNumber, $textMessage)
	{
		return new SmsResult(SmsResult::SMS_FAIL, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this, array());
	}

	/**
	 * A string identifier of the provider. This should be unique across the system.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'twilio';
	}
}
