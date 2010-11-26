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

use \DeskPRO\App;

/**
 * The basic entitiy class
 */
abstract class DomainObject implements \ArrayAccess
{
	const TOARRAY_NOOP = 1;
	const TOARRAY_DEEP = 2;
	const TOARRAY_ONLY_PRIMATIVES = 4;
	const TOARRAY_LOAD_UNLOADED = 6;

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

		foreach ($this->getKeys() as $name) {

			$val = $this[$name];

			if (!($mode & self::TOARRAY_LOAD_UNLOADED)) {
				// If a relation isn't loaded then dont access it, or else we'll lazy load it
				if (!is_scalar($val) AND !is_array($val) AND !\DeskPRO\ORM\Util\Util::isCollectionInitialized($val)) {
					continue;
				}
			}

			if ($mode & self::TOARRAY_NOOP) {

				$values[$name] = $val;

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
	 * Get an array of keys that can be used on this object to access certain data.
	 *
	 * @return array
	 */
	public function getKeys()
	{
		$r = new \ReflectionObject($this);
		$props = $r->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

		$keys = array();
		foreach ($props as $prop) {
			// Skip _props because they arent entity properties
			if ($prop->name[0] === '_') continue;

			$keys[] = $prop->name;
		}

		return $keys;
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
		$orig_name = $name;
		$name = preg_replace('#([A-Z])#', '_$1', $name);

		$match = null;
		if (!preg_match('#^(get|set)_([a-zA-Z0-9_]+)$#', $name, $match)) {
			throw new \BadMethodCallException("Method `$orig_name` is undefined");
		}

		list(, $type, $prop) = $match;
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

	
	/**
	 * @return Doctrine\ORM\EntityRepository
	 */
	public static function getRepository()
	{
		$entity = get_called_class();
		$entity = explode('\\', $entity);
		$entity = array_pop($entity);

		$em = App::getOrm();

		return $em->getRepository("CoreBundle:$entity");
	}
	
	############################################################################
	# ArrayAccess Implementation
	############################################################################

	public function offsetExists($offset)
	{
		$func = "get" . str_replace('_', '', $offset);
		if (method_exists($this, $func)) {
			return true;
		} elseif (property_exists($this, $offset) AND $offset[0] != '_') {
			return true;
		} else {
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
