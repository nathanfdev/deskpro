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

interface ItemInterface
{
	/**
	 * Get the unique typename that identifies this type
	 * 
	 * @return strong
	 */
	public function getType();

	/**
	 * The ID of the type of item, if the type has multiple items (such as custom fields)
	 *
	 * @return int
	 */
	public function getId();

	/**
	 * Sets item data
	 * 
	 * @return void
	 */
	public function setData(array $data);

	/**
	 * Returns item data
	 * 
	 * @return array
	 */
	public function getData();
}