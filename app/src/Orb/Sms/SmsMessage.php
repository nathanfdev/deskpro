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

use Orb\Util\Strings;

/**
 * Represents a single message that needs to be sent. SmsMessages are one or many chunks of 160 characters.
 * Each SmsMessageChunk holds a SmsResult of it's current status.
 *
 * A value object.
 */
class SmsMessage
{
	const STATUS_SUCCESS = 'success';
	const STATUS_PENDING = 'queued';
	const STATUS_FAILED = 'failed';

	/**
	 * @var string full message, without chunking
	 */
	private $rawMessage;

	/**
	 * @var SmsMessageChunk[]|array the split string
	 */
	private $chunks;

	public function __construct($rawMessage)
	{
		$this->rawMessage = $rawMessage;
		$chunks = Strings::splitStringIntoArray($rawMessage, 160);
		$this->chunks = array();
		foreach ($chunks as $chunk) {
			$this->chunks[] = new SmsMessageChunk($chunk);
		}
	}


	/**
	 * This method will advance over time to allow for queuing, pending, etc
	 *
	 * @return bool
	 */
	public function isSent()
	{
		$status = self::STATUS_SUCCESS;

		foreach ($this->chunks as $chunk) {
			if (!$chunk->isSent()) {
				$status = self::STATUS_FAILED;
			}
		}

		return $status == self::STATUS_SUCCESS;
	}


	/**
	 * @return SmsMessageChunk[]
	 */
	public function getChunks()
	{
		return $this->chunks;
	}


	/**
	 * @return bool
	 */
	public function hasMultipleChunks()
	{
		return count($this->chunks) > 1;
	}


	/**
	 * @return string
	 */
	public function getRawMessage()
	{
		return $this->rawMessage;
	}
}
