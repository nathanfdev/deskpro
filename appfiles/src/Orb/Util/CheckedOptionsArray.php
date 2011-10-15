<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

use Orb\Validator\ValidatorInterface;

/**
 * Like a normal options array except that we run validations
 */
class CheckedOptionsArray extends OptionsArray
{
	/**
	 * Array if name=>array(ValidatorInterface)
	 *
	 * @var array
	 */
	protected $validators = array();

	public function ensureRequired(array $required_names)
	{
		$diff = array_diff($required_names, array_keys($this->options));
		if ($diff) {
			throw new CheckedOptionsException("Missing required fields: " . implode(', ', $diff), array('required'), array('names' => $diff));
		}
	}

	public function addCheckedOption($name, ValidatorInterface $validator)
	{
		if (!isset($this->validators[$name])) {
			$this->validators[$name] = array();
		}

		$this->validators[$name][] = $validator;
	}

	public function set($name, $value)
	{
		if (isset($this->validators[$name])) {
			foreach ($this->validators[$name] as $validator) {
				if (!$validator->isValid($value)) {
					throw new CheckedOptionsException("`$name` has an invalid option value", $validator->getErrors(), $validator->getErrorsInfo());
				}
			}
		}

		parent::set($name, $value);
	}
}
