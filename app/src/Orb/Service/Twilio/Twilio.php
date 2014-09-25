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
 * @subpackage
 */

namespace Orb\Service\Twilio;

use Application\DeskPRO\Entity\PhoneNumber;
use Orb\Util\PhoneNumbers;

/**
 * Represents a single twillio api account
 */
class Twilio
{
	/**
	 * @var \Services_Twilio the twilio service, provided by twilio-php sdk
	 */
	protected $twilio;

	/**
	 * @var string the sid
	 */
	protected $sid;

	/**
	 * @var string the auth token
	 */
	protected $auth_token;

	public function __construct($sid, $auth_token)
	{
		$this->sid = $sid;
		$this->auth_token = $auth_token;

		$this->twilio = new \Services_Twilio($sid, $auth_token);
	}

	public function sendSms($toPhoneNumber, $textMessage, $fromPhoneNumber)
	{
		return $this->twilio->account->messages->sendMessage(
			$fromPhoneNumber,
			$toPhoneNumber,
			$textMessage
		);
	}


	/**
	 * @return string a friendly name that the user sets in twillio, usually their email
	 */
	public function getFriendlyName()
	{
		return $this->twilio->accounts->get($this->sid)->friendly_name;
	}


	/**
	 * This method works only for phone numbers already purchased on Twilio. The number must be
	 * SMS capable. Upon call, if the number is valid, we will tell the API to update the
	 * "SmsUrl" and "VoiceUrl" to our service.
	 *
	 * @param        $phone_number a phone number to set the incoming URL config on
	 * @param        $url http url for SMS script
	 * @param string $method http method for SMS script
	 */
	public function setSmsUrl($phone_number, $url, $method = 'POST')
	{
		$request_num = PhoneNumbers::toE164Format($phone_number);
		$numbers = $this->twilio->account->incoming_phone_numbers;
		/** @var \Services_Twilio_Rest_IncomingPhoneNumber $number */
		foreach ($numbers as $number) {
			if ($request_num == PhoneNumbers::toE164Format($number->phone_number)) {
				$number_sid = $number->sid;
				$number = $this->twilio->account->incoming_phone_numbers->get($number_sid);
				$number->update(
					array(
						"SmsUrl"   => "http://demo.twilio.com/docs/sms.xml"
					)
				);
			}
		}

		return null;
	}

	public function getIncomingNumbers()
	{
		$numbers = array();
		$nums = $this->twilio->account->incoming_phone_numbers;
		foreach ($nums as $number) {
			$numbers[$number->friendly_name] = $number->phone_number;
		}

		return $numbers;
	}


	public function setUrlForNumber($url, $number)
	{
		$nums = $this->twilio->account->incoming_phone_numbers;
		foreach ($nums as $num) {
			if (PhoneNumbers::toE164Format($num->phone_number) == PhoneNumbers::toE164Format($number)) {
				// this is the correct number
				$num->update(
					array(
						"SmsUrl"   => $url,
						"SmsMethod" => 'POST'
					)
				);
			}
		}

		return true;
	}
}
