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
 * Orb
 *
 * @package    Orb
 * @subpackage Sms
 */

namespace Orb\Sms;

/**
 * Represents a single text message of 160 characters or less.
 */
class SmsMessageChunk
{
	/**
	 * @var string text message
	 */
	private $text;

	/**
	 * @var SmsResult|null null until a send attempt, then it knows the last SmsResult
	 */
	private $result;

	public function __construct($text, SmsResult $result = null)
	{
		if (strlen($text) > 160) {
			throw new SmsException(
				"SMS text message chunks cannot be greater than 160 characters. Given '$text'"
			);
		}

		$this->text = $text;
		$this->result = $result;
	}


	public function __toString()
	{
		return $this->text ?: '';
	}


	/**
	 * @return bool
	 */
	public function isSent()
	{
		if (!$this->result) {
			return false;
		}

		return $this->result->isSent();
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}


	/**
	 * @return SmsResult
	 */
	public function getResult()
	{
		return $this->result;
	}


	/**
	 * @param null|SmsResult $result
	 */
	public function setResult(SmsResult $result = null)
	{
		$this->result = $result;
	}
}
