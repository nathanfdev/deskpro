<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Item;

abstract class ItemAbstract implements ItemInterface
{
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * Get the item ID
	 * 
	 * @return int
	 */
	public function getId()
	{
		if (isset($this->data['item_id'])) {
			return $this->data['item_id'];
		}

		return null;
	}

	/**
	 * Sets item data
	 *
	 * @return void
	 */
	public function setData(array $data)
	{
		$this->setData = $data;
	}

	/**
	 * Set a speciifc value
	 *
	 * @param string $k
	 * @param mixed $v
	 */
	public function setDataValue($k, $v)
	{
		$this->data[$k] = $v;
	}

	public function getType()
	{
		return get_class($this);
	}

	/**
	 * Returns item data
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	public function __isset($prop)
	{
		return isset($this->data[$prop]);
	}

	public function __get($prop)
	{
		return $this->data[$prop];
	}
}