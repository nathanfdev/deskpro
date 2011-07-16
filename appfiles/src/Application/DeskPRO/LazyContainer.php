<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

/**
 * A very simple container with lazy loading with callback functions for items.
 */
class LazyContainer
{
	protected $items = array();
	protected $wait_items = array();

	public function add($id, $loader)
	{
		$this->wait_items[$id] = $loader;
	}

	public function set($id, $value)
	{
		$this->items[$id] = $value;
	}

	public function has($id)
	{
		return array_key_exists($id, $this->items) OR isset($this->wait_items[$id]);
	}

	public function get($id)
	{
		if (array_key_exists($id, $this->items)) {
			return $this->items[$id];
		}

		if (isset($this->wait_items[$id])) {
			$f = $this->wait_items[$id];
			unset($this->wait_items[$id]);

			$v = $f();

			$this->set($id, $v);
			return $this->items[$id];
		}

		return null;
	}

	public function __get($id)
	{
		return $this->get($id);
	}

	public function __isset($id)
	{
		return $this->has($id);
	}
}