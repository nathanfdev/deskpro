<?php
/**
 * Contains the Orb_Input_Reader_ISource interface.
 *
 * @package Orb
 * @subpackage Input
 */

/**
 * A reader source is a thing that reads variables from somewhere for use with the
 * input reader.
 *
 * @see Orb_Input_Reader
 */
interface Orb_Input_Reader_ISource
{
	/**
	 * Get the value of some variable in the source.
	 *
	 * If $name is an array, then each item if the name and subsequent
	 * keys of an array in the source. For example, if:
	 * <var>$name = array('user', 'name');</var>
	 * Then:
	 * <var>$value = $mysource['user']['name'];</var>
	 *
	 * @param   string|array  $name     The name of the variable
	 * @return  mixed
	 */
	public function getValue($name);



	/**
	 * Check if a value of some variable is set in the source.
	 *
	 * $name follows same rules as getValue().
	 *
	 * @param   string|array  $name     The name of the variable
	 * @return  bool
	 */
	public function checkIsset($name);
}