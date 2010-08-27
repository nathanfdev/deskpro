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

namespace DeskPRO\Bundle\CoreBundle\Entity;

/**
 * The basic entitiy class
 */
abstract class Entity implements \ArrayAccess
{
	/**
	 * An array of properties that have been changed through one of the accessor
	 * methods.
	 * @var array
	 */
	protected $_properties_changed = array();

	/**
	 * Check to see if a certain property has changed.
	 * @return bool
	 */
	public function hasPropertyChanged()
	{
		return in_array($prop, $this->_properties_changed);
	}



	/**
	 * Get a property of this entity. Same as using $entity[something]
	 */
	public function get($name, $default = null)
	{
		return $this->offsetExists($name) ? $this->offsetGet($name) : $default;
	}

	/**
	 * Dynamically implement getX() calls where X is the name of a property.
	 */
	public function __call($name, $arguments)
	{
		if (strpos($name, 'get') !== 0) {
			throw new \BadMethodCallException("`$name` is undefined");
		}

		$prop = substr($name, 3);
		$prop = \preg_replace('#([A-Z])#', '_$1', $prop);
		$prop = substr($prop, 1); // get rid of leading _ cause by above
		$prop = strtolower($prop);

		// Dont allow _ props which are usually protected/private, and make sure it exists
		if ($prop[0] == '_' OR !property_exists($this, $prop)) {
			throw new \BadMethodCallException("Cannot `$name`, the property `$prop` does not exist");
		}

		return $this->$prop;
	}

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
		$func = "set" . str_replace('_', '', $offset);
		if (method_exists($this, $func)) {
			$this->_properties_changed = true;
			$this->$func($value);
		} elseif (property_exists($this, $offset) AND $offset[0] != '_') {
			$this->_properties_changed = true;
			$this->$offset = $value;
		} else {
			throw new \InvalidArgumentException('No such offset exists to set: ' . $offset);
		}
	}

	public function offsetGet($offset)
	{
		$func = "get" . str_replace('_', '', $offset);
		if (method_exists($this, $func)) {
			return $this->$func();
		} elseif (property_exists($this, $offset) AND $offset[0] != '_') {
			return $this->$offset;
		} else {
			throw new \InvalidArgumentException('No such offset exists to get: ' . $offset);
		}
	}

	public function offsetUnset($offset)
	{
		$this->offsetSet($offset, null);
	}
}