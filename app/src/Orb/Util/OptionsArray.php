<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @category Util
 */

namespace Orb\Util;

use Orb\Util\Arrays;

/**
 * Utility functions that work with numbers.
 */
class OptionsArray implements \ArrayAccess, \IteratorAggregate, \Countable
{
	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$this->options = $options;
	}


	/**
	 * Has the option $name
	 *
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->options[$name]);
	}


	/**
	 * Has any of the options named in $names
	 *
	 * @param string[] $names
	 * @return bool
	 */
	public function hasAny(array $names)
	{
		return Arrays::isIn($names, array_keys($this->options), false);
	}


	/**
	 * Has all of the options named in $names
	 *
	 * @param string[] $names
	 * @return bool
	 */
	public function hasAll(array $names)
	{
		return Arrays::isIn($names, array_keys($this->options), true);
	}


	/**
	 * Has any option with the value in $values
	 *
	 * @param string[] $values
	 * @return bool
	 */
	public function hasAnyValue(array $values)
	{
		return Arrays::isIn($values, $this->options, false);
	}


	/**
	 * Has option with all the values in $values
	 *
	 * @param string[] $values
	 * @return bool
	 */
	public function hasAllValues(array $values)
	{
		return Arrays::isIn($values, $this->options, true);
	}


	/**
	 * Get the option named $name or return $default if it doesnt exist
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}


	/**
	 * Set the option $name
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		$this->options[$name] = $value;
	}


	/**
	 * Unset the option $name
	 *
	 * @param string $name
	 */
	public function remove($name)
	{
		unset($this->options[$name]);
	}

	/**
	 * Set $options on the current options. Existing option values will
	 * be overwritten.
	 *
	 * @param array $options
	 */
	public function setArray(array $options)
	{
		foreach ($options as $k => $v) {
			$this->set($k, $v);
		}
	}

	/**
	 * Set $options on the current options. Existing values will NOT be overwritten.
	 *
	 * @param array $options
	 */
	public function setArrayDefault(array $options)
	{
		foreach ($options as $k => $v) {
			if (!array_key_exists($k, $this->options)) {
				$this->set($k, $v);
			}
		}
	}


	/**
	 * Set the option $name if it hasnt been set already
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setDefault($name, $value)
	{
		if (!$this->has($name)) {
			$this->options[$name] = $value;
		}
	}


	/**
	 * Sets the entire options array
	 *
	 * @param array $options
	 */
	public function setAll(array $options)
	{
		$this->options = array();
		$this->setArray($options);
	}


	/**
	 * Gets options as an array
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->options;
	}


	/**#@+ Interface implementation */
	public function __get($name)          { return $this->get($name); }
	public function __set($name, $value)  { $this->set($name, $value); }
	public function __isset($name)        { return $this->has($name); }
	public function	__unset($name)        { return $this->remove($name); }
	public function offsetGet($k)         { return $this->get($k); }
	public function offsetSet($k, $v)     { $this->set($k, $v); }
	public function offsetExists($k)      { return $this->has($k); }
	public function offsetUnset($k)       { $this->remove($k); }
	public function count()               { return count($this->options); }
	public function getIterator()         { return new \ArrayIterator($this->options); }
	/**#@-*/
}
