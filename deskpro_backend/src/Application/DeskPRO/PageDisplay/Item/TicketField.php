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

class TicketField extends TicketItemAbstract
{
	public function getType()
	{
		return 'ticket_field';
	}
}