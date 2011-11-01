<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

class CheckedOptionsException extends \Exception
{
	public $validator_errors;
	public $validator_error_info;

	public function __construct($message, array $errors, array $error_info, $code = 0, $previous = null)
	{
		$this->validator_errors = $errors;
		$this->validator_error_info = $error_info;

		parent::__construct($message, $code, $previous);
	}
}
