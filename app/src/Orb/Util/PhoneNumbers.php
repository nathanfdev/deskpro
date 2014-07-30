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
 * @package Orb
 * @subpackage Util
 */

namespace Orb\Util;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * A wrapper around the php libphonenumber library.
 *
 * Mostly just public static methods to make it easier to use.
 *
 * https://github.com/davideme/libphonenumber-for-PHP
 */
class PhoneNumbers
{
	/**
	 * @param string $phone_number
	 *
	 * @return bool true if it looks like a valid number, false otherwise
	 */
	public static function isValid($phone_number)
	{
		$phone_util = PhoneNumberUtil::getInstance();
		$number = $phone_util->parse($phone_number, null);

		return $phone_util->isValidNumber($number);
	}


	/**
	 * @param string $phone_number
	 *
	 * @return string The phone number is E.164 format
	 */
	public static function toE164Format($phone_number)
	{
		$phone_util = PhoneNumberUtil::getInstance();
		$number = $phone_util->parse($phone_number, null);

		return $phone_util->format($number, PhoneNumberFormat::E164);
	}
}
