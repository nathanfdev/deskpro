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
			throw new CheckedOptionsException("Missing required options: " . implode(', ', $diff), array('required'), array('names' => $diff));
		}

		if ($this->validators) {
			foreach ($this->options as $name => $value) {
				if (isset($this->validators[$name])) {
					foreach ($this->validators[$name] as $validator) {
						if (!$validator->isValid($value)) {
							throw new CheckedOptionsException("`$name` has an invalid option value", $validator->getErrors(), $validator->getErrorsInfo());
						}
					}
				}
			}
		}
	}

	public function addCheckedOption($name, ValidatorInterface $validator)
	{
		if (!isset($this->validators[$name])) {
			$this->validators[$name] = array();
		}

		$this->validators[$name][] = $validator;
	}

	public function addTypeCheckedOption($name, $type, $allow_null = false)
	{
		$fn = function($val) use ($name, $type, $allow_null) {
			if ($val === null && $allow_null) {
				return;
			}

			if (!is_object($val)) {
				return array(array('null_value', array('expected_type' => $type)));
			}
			if (get_class($val) != $type) {
				return array(array('invalid_type', array('expected_type' => $type, 'got_type' => get_class($val))));
			}
		};

		$validator = new \Orb\Validator\Callback(array('callback_function' => $fn));

		$this->addCheckedOption($name, $validator);
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
