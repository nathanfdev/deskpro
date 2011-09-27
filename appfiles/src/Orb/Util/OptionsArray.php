<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

/**
 * Utility functions that work with numbers.
 */
class OptionsArray
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

	public function get($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	public function set($name, $value)
	{
		$this->options[$name] = $value;
	}

	public function setArray(array $options)
	{
		$this->options = array_merge($this->options, $options);
	}

	public function setAll(array $options)
	{
		$this->options = $options;
	}

	public function all()
	{
		return $this->options;
	}
}
