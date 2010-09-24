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
 */
abstract class FieldGroup implements \IteratorAggregate, \Countable
{
	/**
	 * Array of fields
	 * @var array
	 */
	protected $fields = array();



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
		$this->fields[$field->getName()] = $field;
		$field->setParentField($this);
	}


	
	/**
	 * Remove a field from this group
	 * 
	 * @param string $name
	 */
	public function removeFields($name)
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
	 * Returns the iterator for this group.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}
}