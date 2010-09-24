<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Transformer;


/**
 * Transformer composite
 */
class StringBool implements TransformerInterface
{
	/**
	 * Transforms a value into a 1 or 0
	 *
	 * @param  mixed $value     The user input
	 * @return mixed
	 */
	public function transformStoredToForm($value)
	{
		return $value ? '1' : '0';
	}



	/**
	 * Transforms a string where an empty string or 0 is false, otherwise true.
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed            The original form data
	 */
	public function transformFormToStored($value)
	{
		if (trim($value) === '' OR $value == '0') {
			return false;
		}

		return true;
	}
}