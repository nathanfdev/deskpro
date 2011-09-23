<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Validator
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Validator;

class StringEmail extends AbstractValidator
{
	/**
	 * Check $value to see if its valid.
	 *
	 * @return bool
	 */
	protected function checkIsValid($value)
	{
		if (strpos($value, '@') === false) {
			$this->addError('bad_email_format');
			return false;
		}

		list($name, $domain) = explode('@', $value, 2);
		$name   = trim($name);
		$domain = trim($domain);

		if ($name === "" || !$domain) {
			$this->addError('empty_email');
			return false;
		}

		// Match the part before the @
		$regex_name = '#^[a-z0-9!\\#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!\\#$%&\'*+/=?^_`{|}~-]+)*$#i';

		// Match a regular domain name after the @
		$regex_domain = '#^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2,6})$#i';

		// Match a IP address after the @
		$regex_ip = '#^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#i';

		if (!preg_match($regex_name, $name)) {
			$this->addError('bad_email_name');
			return false;
		}

		if (!preg_match($regex_domain, $domain) AND !preg_match($regex_ip, $domain)) {
			$this->addError('bad_email_domain');
			return false;
		}

		return true;
	}
}
