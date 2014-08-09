<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * Orb
 *
 * @package    Orb
 * @subpackage Sms
 */

namespace Orb\Sms\Provider;

use Orb\Service\Twilio\Twilio;
use Orb\Sms\SmsException;
use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsResult;

class TwilioSmsProvider implements SmsProviderInterface
{
	/**
	 * @var Twilio our Twilio service
	 */
	protected $twilio;

	public function __construct($sid, $auth_token)
	{
		$this->twilio = new Twilio($sid, $auth_token);
	}


	/**
	 * @return string a friendly name for the account
	 */
	public function getAccountName()
	{
		return $this->twilio->getFriendlyName();
	}

	/**
	 * @param string $toPhoneNumber   phone number, provider should be able to handle any format
	 * @param string $textMessage     the message to be sent to the given number
	 * @param string $fromPhoneNumber phone number to send to, provider should be able to handle any format
	 *
	 * @throws \Orb\Sms\SmsException
	 * @return \Orb\Sms\SmsResult
	 */
	public function sendMessage($toPhoneNumber, SmsMessageChunk $chunk, $fromPhoneNumber)
	{
		$textMessage = $chunk->getText();

		try {
			$message = $this->twilio->sendSms($toPhoneNumber, $textMessage, $fromPhoneNumber);
		} catch (\Services_Twilio_RestException $e) {
			$result = new SmsResult(
				SmsResult::SMS_FAIL, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this->getName(), array(
					'status'        => $e->getCode(),
					'message'       => $e->getMessage(),
					'twilio_status' => $e->getStatus(),
					'twilio_info'   => $e->getInfo()
				)
			);
			$result->setProviderMessage($e->getStatus().' - '.$e->getMessage().' ('.$e->getCode().')');

			return $result;
		} catch (\Exception $e) {
			$result = new SmsResult(
				SmsResult::SMS_FAIL, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this->getName(), array(
					'status'  => $e->getCode(),
					'message' => $e->getMessage()
				)
			);
			$result->setProviderMessage($e->getCode().' - '.$e->getMessage());

			return $result;
		}

		// we successfully sent a valid SMS to Twilio
		$result = new SmsResult(
			SmsResult::SMS_SENT, $fromPhoneNumber, $toPhoneNumber, $textMessage, $this->getName(), array(
				'sid'             => $message->sid, // this can later be used to find the status of the sms
				'num_segments'    => $message->num_segments,
				'provider_status' => $message->status
			)
		);

		return $result;
	}


	/**
	 * @throws \Orb\Sms\SmsException
	 * @return array an array of arrays in the format:
	 *               array( 'display_name' => 'Some Name', 'phone_number' => '+19023340390 )
	 */
	public function getIncomingNumbers()
	{
		try {
			$out = array();

			$nums = $this->twilio->getIncomingNumbers();
			foreach ($nums as $display => $number) {
				$out[] = array('display_name' => $display, 'phone_number' => $number);
			}

			return $out;
		} catch (\Exception $e) {
			throw new SmsException('could not get incoming numbers from Twilio provider');
		}
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
