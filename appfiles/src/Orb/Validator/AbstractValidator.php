<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Validator
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Validator;

/**
 * Validates a value
 */
class AbstractValidator implements ValidatorInterface
{
	/**
	 * An array of simple error codes. Language must be handled elsewhere.
	 * 
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Sometimes an error might have additional information, such as a position or
	 * context where an error took place. This should be an array of errorcode=>info
	 * that could be used in some other system to report errors to a user
	 * 
	 * @var array
	 */
	protected $errors_info = array();



	/**
	 * Check to see if a value is valid or not.
	 *
	 * @return bool
	 */
	public function isValid($value)
	{
		// Reset
		$this->errors = array();
		$this->errors_info = array();

		return $this->checkIsValid($value);
	}

	

	/**
	 * Check to see if a value is valid or not.
	 * 
	 * @param mixed $value
	 * @return bool
	 */
	public function __invoke($value)
	{
		return $this->isValid($value);
	}

	

	/**
	 * Check $value to see if its valid.
	 *
	 * @return bool
	 */
	abstract protected function checkIsValid($value);



	/**
	 * Get an array of error codes
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	
	/**
	 * Get an array of errcode=>info. Null means no info available.
	 *
	 * @return array
	 */
	public function getErrorsInfo()
	{
		$ret = array();
		foreach ($this->errors as $k) {
			$ret[$k] = isset($this->errors_info[$k]) ? $this->errors_info[$k] : null;
		}

		return $ret;
	}

	

	/**
	 * Add an error to the errors array.
	 *
	 * @param  string  $code        The error code to add
	 * @param  mixed   $error_info  Additional info that can help explain the error
	 */
	protected function addError($code, $error_info = null)
	{
		$this->errors[] = $code;
		array_unique($this->errors);

		if ($error_info !== null) {
			$this->errors_info[$code] = $error_info;
		}
	}
}