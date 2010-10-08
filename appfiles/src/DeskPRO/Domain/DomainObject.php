<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Domain;

/**
 * The basic entitiy class
 */
abstract class DomainObject implements \ArrayAccess
{
	const TOARRAY_NOOP = 0;
	const TOARRAY_DEEP = 1;
	const TOARRAY_ONLY_PRIMATIVES = 2;

	/**
	 * An array of properties that have been changed through one of the accessor
	 * methods.
	 * @var array
	 */
	protected $_properties_changed = array();

	public function __construct(array $params = array())
	{
		$this->init($params);
	}



	/**
	 * Init. Will be passed an array of params.
	 */
	protected function init(array $params)
	{

	}


	/**
	 * Set values from an array
	 * @param array $values The values to set
	 */
	public function fromArray(array $values)
	{
		foreach ($values as $k => $v) {
			$this[$k] = $v;
		}
	}


	
	/**
	 * Get a simple array representation of this entity
	 *
	 * @param bool $mode
	 * @return array
	 */
	public function toArray($mode = self::TOARRAY_NOOP)
	{
		$values = array();

		$r = new \ReflectionObject($this);
		$props = $r->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED);

		foreach ($props as $prop) {
			// Skip _props because they arent entity properties
			if ($prop->name[0] === '_') continue;

			$name = $prop->name[0];
			$val = $this->$name;

			if ($mode & self::TOARRAY_NOOP) {
				$values[$name] = $this->$name;

			} elseif ($mode & self::TOARRAY_ONLY_PRIMATIVES) {
				if (is_scalar($val) OR is_array($val)) {
					$values[$name] = $val;
				}

			} elseif ($mode & self::TOARRAY_DEEP) {
				if (is_object($val) AND method_exists($val, 'toArray')) {
					// If its a DomainObject then we can pass on the mode
					if ($this->$name instanceof DomainObject) {
						$val = $val->toArray($mode);
					// Otherwise it could be some other implementation, so we dont know how to handle it
					} else {
						$val = $val->toArray();
					}
				}
				$values[$name] = $val;
			}
		}

		return $values;
	}

	

	/**
	 * Check to see if a certain property has changed.
	 * @return bool
	 */
	public function hasPropertyChanged($prop)
	{
		return in_array($prop, $this->_properties_changed);
	}



	/**
	 * Get a property of this entity. Same as using $entity[something]
	 *
	 * @param string $name The property to get
	 */
	public function get($name)
	{
		return $this->offsetGet($name);
	}

	

	/**
	 * Set the value of a property. Same as using $entity[something]
	 *
	 * @param string $name The property to set
	 * @param mixed $value The value to set
	 */
	public function set($name, $value)
	{
		$this->offsetSet($name, $value);
	}
	


	/**
	 * Hook method called when a property has been changed.
	 *
	 * @param string $name The property that was changed
	 * @param mixed $old_value The old value
	 */
	protected function onPropertyChanged($property, $old_value)
	{

	}



	/**
	 * Dynamically implement getX and setX methods.
	 */
	public function __call($name, $arguments)
	{
		$match = null;
		if (!preg_match('#^(get|set)([a-zA-Z0-9]+)$#', $name, $match)) {
			throw new \BadMethodCallException("Method `$name` is undefined");
		}

		list($type, $prop) = $match;

		$prop = preg_replace('#([A-Z])#', '_$1', $prop);
		$prop = substr($prop, 1); // remove leading _x cause by above setWhateverField _whatever_field
		$prop = strtolower($prop);

		// getX
		if ($type == 'get') {
			return $this[$prop];

		// setX
		} else {
			if (!isset($arguments[0])) {
				$arguments = array(null);
			}
			$this[$prop] = $arguments[0];
		}
	}




	
	############################################################################
	# ArrayAccess Implementation
	############################################################################

	public function offsetExists($offset)
	{
		try {
			$this->offsetGet($offset);
			return true;
		} catch (\InvalidArgumentException $e) {
			return false;
		}
	}



	public function offsetSet($offset, $value)
	{
		$old_value = $this[$offset];

		// No change
		if ($old_value == $value) {
			return;
		}

		$func = "set" . str_replace('_', '', $offset);
		if (method_exists($this, $func)) {
			$this->$func($value);
		} else {
			$this->$offset = $value;
		}

		$this->_properties_changed[] = $offset;
		$this->onPropertyChanged($offset, $old_value);
	}



	public function offsetGet($offset)
	{
		$func = "get" . str_replace('_', '', $offset);
		if (method_exists($this, $func)) {
			return $this->$func();
		} elseif (property_exists($this, $offset) AND $offset[0] != '_') {
			return $this->$offset;
		} else {
			throw new \InvalidArgumentException('No such offset exists: ' . $offset);
		}
	}



	public function offsetUnset($offset)
	{
		$this->offsetSet($offset, null);
	}
}
