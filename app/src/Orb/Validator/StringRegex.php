<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Validator
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Validator;

class StringRegex extends AbstractValidator
{
	protected $regex;

	public function init()
	{
		$this->regex = $this->getOption('regex');
	}

	/**
	 * Check $value to see if its valid.
	 *
	 * @return bool
	 */
	protected function checkIsValid($value)
	{
		if (!preg_match($this->regex, $value)) {
			$this->addError('no_regex_match');
			return false;
		}

		return true;
	}
}
