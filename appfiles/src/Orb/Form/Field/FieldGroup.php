<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A form field.
 *
 * Inspired by sf2's Field.
 */
abstract class FieldGroup
{
	/**
	 * Array of fields
	 * @var array
	 */
	protected $fields = array();


	public function addField(Field $field)
	{
		$this->fields[$field->getName()] = $field;
	}


	public function getField()
	{
		return $this->fields;
	}
}