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
 * A transformer transforms form input from a user into data we can use (for example storage
 * in a database).
 *
 * For example, a form might have a date field with 3 separate inputs for YYYY-MM-DD.
 * transform() might read the three fields and returns a \DateTime. reverseTransform()
 * might take a \DateTime and convert it back into the fields required for the input.
 */
interface TransformerInterface
{
	/**
	 * Transforms a field input into data we can use.
	 *
	 * @param  mixed $value     The user input
	 * @return mixed
	 */
	public function transform($value);



	/**
	 * Transforms data stored into data we can put into a form.
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed            The original form data
	 */
	public function reverseTransform($value);
}