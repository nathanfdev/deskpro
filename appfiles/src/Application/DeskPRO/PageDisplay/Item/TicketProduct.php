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

class TicketProduct extends TicketItemAbstract
{
	public function getType()
	{
		return 'ticket_product';
	}

	public function compileJsCheck()
	{
		$js_ids = Arrays::castToType($this->data['products'], 'int');
		$js_ids = "[" . implode(',', $js_ids) . "]";

		$js = "if ($js_ids.indexOf(reader.getProductId() !== -1) return true; else return false;";

		return $js;
	}
}