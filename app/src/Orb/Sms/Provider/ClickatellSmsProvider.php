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

use Orb\Sms\SmsMessageChunk;
use Orb\Sms\SmsProviderInterface;
use Bdt\Clickatell\ClickatellClient;
use Orb\Sms\SmsResult;

class ClickatellSmsProvider implements SmsProviderInterface
{
	/**
	 * @var ClickatellClient
	 */
	private $client;

	/**
	 * @var string
	 */
	private $user;

	/**
	 * @var string
	 */
	private $apiId;

	/**
	 * @var string
	 */
	private $password;


	/**
	 * @param $user The Clickatell User
	 * @param $apiId The Clickatell API ID
	 * @param $password The Clickatell password
	 */
	public function __construct($user, $password, $apiId)
	{
		$this->client = ClickatellClient::factory(array( 'api_id'   => $apiId, 'user' => $user,
		                                                 'password' => $password ));
		$this->user = $user;
		$this->apiId = $apiId;
		$this->password = $password;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendMessage($toPhoneNumber, SmsMessageChunk $chunk, $fromPhoneNumber)
	{
		$textMessage = $chunk->getText();

		try {
			$result = $this->client->getCommand('SendMsg',
				array( 'to' => $toPhoneNumber, 'text' => $textMessage, ))->execute();
		} catch (\Exception $e) {
			$smsResult = new SmsResult(SmsResult::SMS_FAIL,
				$fromPhoneNumber,
				$toPhoneNumber,
				$textMessage,
				$this->getName(),
				array( 'status' => $e->getCode(), 'message' => $e->getMessage() ));

			return $smsResult;
		}

		if ($result->isSuccessful()) {
			$smsResult = new SmsResult(SmsResult::SMS_SENT,
				$fromPhoneNumber,
				$toPhoneNumber,
				$textMessage,
				$this->getName(),
				$result->getMessageIds());
		} else {
			$smsResult = new SmsResult(SmsResult::SMS_FAIL,
				$fromPhoneNumber,
				$toPhoneNumber,
				$textMessage,
				$this->getName(),
				$result->getMessageIds());
		}

		return $smsResult;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'clickatell';
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParams()
	{
		return array(
			'user' => $this->user,
			'password' => $this->password,
			'api_id' => $this->apiId
		);
	}
}
