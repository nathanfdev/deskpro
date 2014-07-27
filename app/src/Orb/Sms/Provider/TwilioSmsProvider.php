<?php

namespace Orb\Sms\Provider;

use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;

class TwilioSmsProvider implements SmsProviderInterface
{
	protected $twilio;

	public function __construct($sid, $auth_token)
	{
		$this->twilio = new \Services_Twilio($sid, $auth_token);
	}

	/**
	 * @param string $toPhoneNumber   phone number, provider should be able to handle any format
	 * @param string $textMessage     the message to be sent to the given number
	 * @param string $fromPhoneNumber phone number to send to, provider should be able to handle any format
	 *
	 * @throws \Orb\Sms\SmsException
	 * @return \Orb\Sms\SmsResult
	 */
	public function sendMessage($toPhoneNumber, $textMessage, $fromPhoneNumber)
	{
		try {
			$message = $this->twilio->account->messages->sendMessage(
				$fromPhoneNumber,
				$toPhoneNumber,
				$textMessage
			);
		} catch (\Services_Twilio_RestException $e) {
			$result = new SmsResult(
				SmsResult::SMS_FAIL, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this, array(
					'status'        => $e->getCode(),
					'message'       => $e->getMessage(),
					'twilio_status' => $e->getStatus(),
					'twilio_info'   => $e->getInfo()
				)
			);

			return $result;
		} catch (\Exception $e) {
			$result = new SmsResult(
				SmsResult::SMS_FAIL, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this, array(
					'status'  => $e->getCode(),
					'message' => $e->getMessage()
				)
			);

			return $result;
		}

		// we successfully sent a valid SMS to Twilio
		$result = new SmsResult(
			SmsResult::SMS_SENT, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this, array(
				'sid'             => $message->sid, // this can later be used to find the status of the sms
				'num_segments'    => $message->num_segments,
				'provider_status' => $message->status
			)
		);

		return $result;
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
