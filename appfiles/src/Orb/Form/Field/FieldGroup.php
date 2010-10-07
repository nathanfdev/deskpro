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
 * A field that has any number of sub-fields
 *
 * This can be used to group things like radio fields, or just serve as a namespace
 * because fields in forms are named after their parents.
 *
 * @option string render_field_wrapepr  An HTML string to wrap each field if you choose
 *                                      to render this field group (as opposed to just using it
 *                                      as a container. Use %s as the field HTML.
 *                                      Default: <div class="group-field">%s</div>
 *                                      NOTE: applies to Basic renderer only
 */
class FieldGroup extends Field implements \IteratorAggregate, \Countable, \ArrayAccess
{
	/**
	 * Array of fields
	 * @var array
	 */
	protected $fields = array();

	/**
	 * An array of allowed types
	 * @see addAllowedType
	 * @var array
	 */
	protected $allowed_types = array();


	/**
	 * This is meant for subclasses so they can restrict which types of fields are in a group.
	 * If the array is empty, then all types are allowd.
	 *
	 * Note this only affects THIS groups 'addField' method. Subgroups are not affected, the restriction
	 * is not delegated down the hierarchy.
	 */
	protected function addAllowedFieldType($field_type)
	{
		$this->allowed_types[] = $field_type;
	}



	/**
	 * Check if a field is allowed in this group. This runs the field through the allowed_types
	 * array.
	 *
	 * @param Field $field
	 * @return bool
	 */
	public function isFieldAllowed(Field $field)
	{
		if ($this->allowed_types AND !\in_array(get_class($field), $this->allowed_types)) {
			return false;
		}

		return true;
	}



	/**
	 * Set an array of values for fields in this group
	 *
	 * @param array $data
	 */
	public function setData($data)
	{
		if (!is_array($this->data)) {
			throw new \InvalidArgumentException('$data must be an array');
		}

		parent::setData($data);

		foreach ($this->data as $k => $v) {
			if ($this->hasField($k)) {
				$this->getField($k)->setData($v);
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
			if ($this->hasField($k)) {
				$this->getField($k)->setFormData($v);
			}
		}
	}


	
	/**
	 * Return array of all data values
	 *
	 * @return array
	 */
	public function getData()
	{
		$values = array();

		foreach ($this->fields as $f) {
			$values[$f->getName()] = $f->getData();
		}

		return $values;
	}


	
	/**
	 * Return an array of all form values
	 * @return array
	 */
	public function getFormData()
	{
		$values = array();

		foreach ($this->fields as $f) {
			$values[$f->getName()] = $f->getFormData();
		}

		return $values;
	}


	/**
	 * Checks the entire group to see if its valid
	 * @return bool
	 */
	public function isValid()
	{
		if (!parent::isValid()) return false;

		foreach ($this->fields as $f) {
			if (!$f->isValid()) {
				return false;
			}
		}

		return true;
	}



	/**
	 * Add a new field to the group
	 *
	 * @param Field $field
	 */
	public function addField(Field $field)
	{
		if (!$this->isFieldAllowed($field)) {
			throw new \InvalidArgumentException('Invalid Field type `' . get_class($field) . '`. The field type is not allowed in this group.');
		}

		$this->fields[$field->getName()] = $field;
		$field->setParentField($this);
	}


	
	/**
	 * Remove a field from this group
	 * 
	 * @param string $name
	 */
	public function removeField($name)
	{
		$field = $this->getField($name);
		$field->setParent(null);

		unset($this->fields[$name]);
	}

	

	/**
	 * Get a field from this group
	 *
	 * @param string $name
	 * @return Field
	 */
	public function getField($name)
	{
		if (!isset($this->fields[$name])) {
			return \OutOfBoundsException('No field with name `' . $name . '`');
		}
		
		return $this->fields[$name];
	}


	
	/**
	 * Find a field that might be deep in the hierarchy. Separate groups by dots.
	 *
	 * @param string $name
	 * @return Field
	 */
	public function findField($name)
	{
		$name_parts = explode('.', $name);

		$current_field = $this;
		while ($name_parts) {
			$part = array_shift($name_parts);
			if (!$current_field->hasField($part)) {
				return null;
			}
			$current_field = $current_field->getField($part);
		}

		return $current_field;
	}


	
	/**
	 * Go through the collection and find all fields of a certain type.
	 *
	 * @param  string  $type       The field classname
	 * @param  bool    $recursive  To recurse in sub-groups as well
	 * @return bool
	 */
	public function findFieldsOfType($type, $recursive = true)
	{
		$ret = array();

		foreach ($this as $f) {
			if ($f instanceof $type) {
				$ret[] = $f;
				if ($recursive AND $f instanceof FieldGroup) {
					$ret = array_merge($ret, $f->findFieldsOfType($type, true));
				}
			}
		}

		return $ret;
	}

	
	
	/**
	 * Check to see if a field exists in this group
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasField($name)
	{
		return isset($this->fields[$name]);
	}


	
	/**
	 * Get all fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}


	
	/**
	 * Count how many fields there are
	 * @return int
	 */
	public function count()
	{
		return count($this->fields);
	}



	/**
	 * Is this an actual composite field, rather than just a group of fields?
	 *
	 * @see CompositeField
	 * @return bool
	 */
	public function isCompositeField()
	{
		return false;
	}


	
	/**
	 * Returns the iterator for this group.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}


	
	public function offsetExists($offset)
	{
		return $this->hasField($offset);
	}

	public function offsetGet($offset)
	{
		return $this->getField($offset);
	}

	public function offsetSet($offset, $value)
	{
		throw new \BadMethodCallException('Use addField()');
	}

	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException('Use removeField()');
	}
}