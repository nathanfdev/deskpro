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

namespace DeskPRO\Entities;

/**
 * The basic entitiy class
 */
abstract class Entity implements \ArrayAccess
{
	public function offsetExists($offset)
	{
		try {
			$val = $this->offsetGet($offset);
		} catch (InvalidArgumentException $e) {
			$val = null;
		}
		return $val !== null;
	}

	public function offsetSet($offset, $value)
	{
		$func = "set$offset";
		if (method_exists($this, $func)) {
			$this->$func($value);
		} elseif (property_exists($this, $offset)) {
			$this->$offset = $value;
		}

		throw new InvalidArgumentException('No such offset exists to set: ' . $offset);
	}

	public function offsetGet($offset)
	{
		$func = "get$offset";
		if (method_exists($this, $func)) {
			return $this->$func();
		} elseif (property_exists($this, $offset)) {
			return $this->$offset;
		}

		throw new InvalidArgumentException('No such offset exists to get: ' . $offset);
	}

	public function offsetUnset($offset)
	{
		$this->offsetSet($offset, null);
	}
}