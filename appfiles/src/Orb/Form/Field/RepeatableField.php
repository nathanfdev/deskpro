<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A repeatable field is any field that can be duplicated for multiple values.
 * For example, if a user can input multiple arbitrary date values, we can collect
 * them into an array using the RepeatableField.
 *
 * In the renderer you might use Javascript to add a [+] to add more fields.
 */
class RepeatableField extends CompositeField
{
	/**
	 * The basic field.
	 * @var Field
	 */
	protected $base_field;



	/**
	 * Set the base field. This is the field we'll handle; each new value will be
	 * a clone of this field.
	 *
	 * Note you should add at least one of these fields to this field group to represent
	 * the "new" one that a user can edit. (Or you might leave this up to the renderer using JS etc)
	 * 
	 * @param Field $field
	 */
	public function setBaseField(Field $field)
	{
		$this->base_field = $field;
	}


	
	/**
	 * Add an empty field , the "new" one the user can edit.
	 *
	 * @param string $name The name of the new field
	 */
	public function addEmptyField($name)
	{
		$new = clone $this->base_field;

		$this->add($new);
	}

	

	/**
	 * Set an array of values for fields in this group
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		if (!is_array($data) AND !($data instanceof \Traversable)) {
			throw new \InvalidArgumentException('$data must be an array, got ' . \Orb\Util\Util::typeof($data));
		}

		parent::setData($data);

		foreach ($this->data as $k => $v) {
			// If it exists, we'll update the value
			if ($this->hasField($k)) {
				$this->getField($k)->setData($v);

			// Otherwise we'll add it
			} else {
				$new_field = clone $this->base_field;
				$new_field->setData($v);
				$this->add($new_field);
			}
		}
	}



	/**
	 * Set an array of form values for fields in this group
	 *
	 * @param array $data
	 */
	public function setFormData($form_data)
	{
		if (!is_array($form_data)) {
			throw new \InvalidArgumentException('$form_data must be an array');
		}

		parent::setFormData($form_data);

		foreach ($this->form_data as $k => $v) {

			// If it exists, we'll update the value
			if ($this->hasField($k)) {
				$this->getField($k)->setFormData($v);

			// Otherwise we'll just add it
			} else {
				$new_field = clone $this->base_field;
				$new_field->setFormData($v);
				$this->add($new_field);
			}
		}
	}
}