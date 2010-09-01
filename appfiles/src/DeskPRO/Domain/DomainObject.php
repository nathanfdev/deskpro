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
	/**
	 * @var \Symfony\Component\DependencyInjection\Container
	 */
	protected $_container;

	/**
	 * An array of properties that have been changed through one of the accessor
	 * methods.
	 * @var array
	 */
	protected $_properties_changed = array();


	/**
	 * Create a ne winstance of the entity.
	 *
	 * Any <var>$param</var> parameters specified will be passed to the init() method.
	 *
	 * @param \Symfony\Component\DependencyInjection\Container $container
	 * @param array $params An array of user data that will be passed to init
	 */
	public function __construct(\Symfony\Component\DependencyInjection\Container $container, array $params = array())
	{
		if ($container) {
			$this->setContainer($container);
		}

		$this->init($params);
	}



	/**
	 * Init. Will be passed an array of params.
	 */
	protected function init()
	{

	}



	/**
	 * Set the container
	 *
	 * @param Symfony\Component\DependencyInjection\Container $container
	 */
	public function setContainer(\Symfony\Component\DependencyInjection\Container $container)
	{
		$this->_container = $container;
	}



	/**
	 * Get the set container
	 *
	 * @return \Symfony\Component\DependencyInjection\Container
	 */
	public function getContainer()
	{
		if (!$this->_container) {
			throw new UnexpectedValueException('The container has not been set!');
		}

		return $this->_container;
	}



	/**
	 * Has a container been set?
	 *
	 * @return bool
	 */
	public function hasContainer()
	{
		return (bool)$this->_container;
	}



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
