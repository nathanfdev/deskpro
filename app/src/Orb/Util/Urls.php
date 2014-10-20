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
 * @package Orb
 * @category Util
 */

namespace Orb\Util;

/**
 * Url utility functions.
 *
 * @static
 */
class Urls
{
	/**
	 * Verifies if $email is from the $domain
	 *
	 * example:
	 * chris.tickner@gmail.com  and  deskpro.com  FALSE
	 * chris.tickner@deskpro.com  and  deskpro.com  TRUE
	 * chris.tickner@support.deskpro.com and deskpro.com FALSE
	 * chris.tickner@support.deskpro.com and support.deskpro.com TRUE
	 *
	 * @param string $email the email that we are checking vs the domain name
	 * @param string $domain just a domain name
	 * @return bool true if $domain is the extact domain used in the email of $email
	 * @throws \InvalidArgumentException
	 */
	public static function verifyEmailDomain($email, $domain)
	{
		if (preg_match('#^http#', $domain)) {
			$domain = Strings::extractRegexMatch('#^https?://(.*?)/?.*?$#', $domain);
		}

		$domain = trim($domain);
		$domain = trim($domain, '/');

		if (!$domain) {
			throw new \InvalidArgumentException('must provide a valid domain to Urls::verifyEmailDomain');
		}

		$email        = trim($email);
		$email_array  = explode('@', $email);
		$email_domain = array_pop($email_array);;

		return $email_domain === $domain;
	}
}
