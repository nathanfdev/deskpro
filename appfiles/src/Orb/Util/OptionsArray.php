<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

use Orb\Util\Arrays;

/**
 * Utility functions that work with numbers.
 */
class OptionsArray implements \ArrayAccess, \IteratorAggregate
{
	protected $options = array();

	public function __construct(array $options = array())
	{
		$this->options = $options;
	}

	public function has($name)
	{
		return isset($this->options[$name]);
	}

	public function hasAny(array $names)
	{
		return Arrays::isIn($this->options, $names, false);
	}

	public function hasAll(array $names)
	{
		return Arrays::isIn($this->options, $names, true);
	}

	public function get($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	public function set($name, $value)
	{
		$this->options[$name] = $value;
	}

	public function remove($name)
	{
		unset($this->options[$name]);
	}

	public function setArray(array $options)
	{
		$this->options = array_merge($this->options, $options);
	}

	public function setArrayDefault(array $options)
	{
		$this->options = array_merge($options, $this->options);
	}

	public function setAll(array $options)
	{
		$this->options = $options;
	}

	public function all()
	{
		return $this->options;
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	public function __isset($name)
	{
		return $this->has($name);
	}

	public function	__unset($name)
	{
		return $this->remove($name);
	}

	public function offsetGet($k)
	{
		return $this->get($k);
	}

	public function offsetSet($k, $v)
	{
		$this->set($k, $v);
	}

	public function offsetExists($k)
	{
		return $this->has($k);
	}

	public function offsetUnset($k)
	{
		$this->remove($k);
	}

	public function getIterator()
	{
		return new ArrayIterator($this->options);
	}
}
