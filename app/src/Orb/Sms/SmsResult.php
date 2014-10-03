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
 * @package    Orb
 * @subpackage Sms
 */

namespace Orb\Sms;

/**
 * Tries to capture various information about a SMS send request (API request responses) in
 * a semi-abstract way. An array of provider specific details might be provided as well (metadata).
 *
 * This is meant to be serialized and stored for logging or historical purposes.
 */
class SmsResult
{
	/**
	 * SMS_SEND means WE successfully handed off the SMS to the provider, the provider may queue the message to be
	 * sent at a later time, or it may send it immediately, this status simply means we did our part to tell them to send.
	 */
	const SMS_SENT = 'sent';

	/**
	 * The provider told us there was a problem with the message and refused to send it.
	 */
	const SMS_FAIL = 'fail';

	private $status;
	private $from_number;
	private $to_number;
	private $message;
	private $provider;
	private $provider_metadata;

	/**
	 * @var string optional - usually just used for error logging/debug purposes
	 */
	private $provider_message;

	/**
	 * @param string $status            the status of the sms send request (a const value of this class)
	 * @param string $from_number       the number from
	 * @param string $to_number         the number to
	 * @param string $message           the sent message
	 * @param string $providerId        the value of the provider's getName() method
	 * @param array  $provider_metadata an array of provider-specific metadata about a SMS sent api request
	 *                                  the $provider_metadata array will be serialized
	 */
	public function __construct(
		$status,
		$from_number,
		$to_number,
		$message,
		$providerId,
		array $provider_metadata
	) {
		$this->setStatus($status);
		$this->to_number = $to_number;
		$this->message = $message;
		$this->provider = $providerId;
		$this->provider_metadata = $provider_metadata;
		$this->from_number = $from_number;
	}

	/**
	 * @return bool convenience method to check if status is sent
	 */
	public function isSent()
	{
		return self::SMS_SENT == $this->status;
	}

	/**
	 * @return bool convenience method to check if status is failed
	 */
	public function isFail()
	{
		return self::SMS_FAIL == $this->status;
	}

	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param mixed $status
	 */
	public function setStatus($status)
	{
		if (!in_array($status, array(self::SMS_SENT, self::SMS_FAIL))) {
			throw new \InvalidArgumentException(sprintf('Invalid SMS status "%s"', $status));
		}

		$this->status = $status;
	}

	/**
	 * @return mixed
	 */
	public function getToNumber()
	{
		return $this->to_number;
	}

	/**
	 * @param mixed $to_number
	 */
	public function setToNumber($to_number)
	{
		$this->to_number = $to_number;
	}

	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param mixed $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @return mixed
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @param SmsProviderInterface $provider
	 */
	public function setProvider(SmsProviderInterface $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * @return mixed
	 */
	public function getProviderMetadata()
	{
		return $this->provider_metadata;
	}

	/**
	 * @param mixed $provider_metadata
	 */
	public function setProviderMetadata(array $provider_metadata)
	{
		$this->provider_metadata = $provider_metadata;
	}

	/**
	 * @return mixed
	 */
	public function getFromNumber()
	{
		return $this->from_number;
	}

	/**
	 * @param mixed $from_number
	 */
	public function setFromNumber($from_number)
	{
		$this->from_number = $from_number;
	}

	/**
	 * @return mixed
	 */
	public function getProviderMessage()
	{
		return $this->provider_message;
	}

	/**
	 * @param mixed $provider_message
	 */
	public function setProviderMessage($provider_message)
	{
		$this->provider_message = $provider_message;
	}
}
