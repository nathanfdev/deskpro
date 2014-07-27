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
 * @package Orb
 * @subpackage Sms
 */

namespace Orb\Sms;

use Orb\Sms\SmsException;

/**
 * Responsible for sending SMS messages.
 *
 * The SmsSender can use any provider to send any message.
 *
 * If the same SmsProvider and/or From Number are used to send many messages, you
 * can set the default SmsProvider and From Number and omit them from the send methods
 * for your convenience.
 */
class SmsSender
{
	/**
	 * @var SmsProviderInterface the default provider, used in the cases where the send method gets a null for Provider
	 */
	protected $default_provider;

	/**
	 * @var string the default From Number, used in the cases where the send methods get a null for From Number.
	 */
	protected $from_number;

	/**
	 * @param SmsProviderInterface $provider a quick way to set the default provider (optional)
	 * @param string $from_number a quick way to set the default from number (optional)
	 */
	public function __construct(SmsProviderInterface $provider = null, $from_number = null)
	{
		$this->default_provider = $provider;
		$this->from_number = $from_number;
	}

	/**
	 * Send the $message to $to_number using the given $from_number and $provider.
	 *
	 * $from_number and $provider are optional, and fall back on the set default values.
	 *
	 * A $provider is needed (here or as a default) to send a message, but a From Number can be
	 * null when sending a message if the provider does not need it.
	 *
	 * This method is meant to be overwritten by subclasses (different types of SmsSenders).
	 *
	 * @param string               $to_number
	 * @param string               $message
	 * @param string|null          $from_number
	 * @param SmsProviderInterface $provider
	 * @return SmsResult
	 * @throws SmsException
	 */
	public function send($to_number, $message, $from_number = null, SmsProviderInterface $provider = null)
	{
		return $this->doSend($to_number, $message, $from_number, $provider);
	}

	/**
	 * @param $from_number
	 *
	 * @return string|null
	 */
	protected function getFromNumber($from_number)
	{
		return $from_number ?: $this->getDefaultFromNumber();
	}

	/**
	 * @param $provider
	 *
	 * @return SmsProviderInterface
	 */
	protected function getProvider(SmsProviderInterface $provider = null)
	{
		return $provider ?: $this->getDefaultProvider();
	}

	/**
	 * @param SmsProviderInterface $provider
	 */
	public function setDefaultProvider(SmsProviderInterface $provider)
	{
		$this->default_provider = $provider;
	}

	/**
	 * @return SmsProviderInterface
	 */
	public function getDefaultProvider()
	{
		return $this->default_provider;
	}

	/**
	 * @param string|null $from_number
	 */
	public function setDefaultFromNumber($from_number)
	{
		$this->from_number = $from_number;
	}

	/**
	 * @return string
	 */
	public function getDefaultFromNumber()
	{
		return $this->from_number;
	}

	/**
	 * This is called from within the send() method. It's arguments and return values are the same.
	 *
	 * This allows subclasses to reuse this sending logic, if they want, and wrap it with other functionality.
	 *
	 * @param string               $to_number
	 * @param string               $message
	 * @param string|null          $from_number
	 * @param SmsProviderInterface $provider
	 * @return SmsResult
	 * @throws SmsException
	 */
	protected function doSend($to_number, $message, $from_number = null, SmsProviderInterface $provider = null)
	{
		if (!$provider = $this->getProvider($provider)) {
			throw new SmsException('cannot send SMS without an SmsProvider');
		}

		$from = $this->getFromNumber($from_number);

		return $provider->sendMessage($from, $to_number, $message);
	}
}
