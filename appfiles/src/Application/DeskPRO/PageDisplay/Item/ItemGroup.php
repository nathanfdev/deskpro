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

use Application\DeskPRO\PageDisplay\Item\ItemInterface;

class ItemGroup extends ItemAbstract
{
	/**
	 * @var \Application\DeskPRO\PageDisplay\Item\ItemGroup[]
	 */
	protected $items = array();

	public function getType()
	{
		return 'item_group';
	}

	public function addItem(ItemInterface $item)
	{
		$this->items[] = $item;
	}

	public function addItems(array $items)
	{
		foreach ($items as $item) {
			$this->addItem($item);
		}
	}

	public function getItems()
	{
		return $this->items;
	}
}