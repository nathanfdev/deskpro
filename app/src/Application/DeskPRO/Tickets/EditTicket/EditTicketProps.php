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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 */

namespace Application\DeskPRO\Tickets\EditTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * This wraps up the 'ticket' data of a newticket
 */
class EditTicketProps implements \ArrayAccess
{
	protected static $prop_names = array(
		'subject' => 1, 'department_id' => 1, 'category_id' => 1,
		'priority_id' => 1, 'product_id' => 1, 'cc_emails' => 1, 'remove_ccs' => 1
	);

	public $subject = '';

	public $department_id = 0;
	public $category_id   = 0;
	public $priority_id   = 0;
	public $product_id    = 0;
	public $cc_emails = '';
	public $remove_ccs = array();

	public function __construct(Ticket $ticket)
	{
		$this->department_id  = $ticket->department ? $ticket->department->getId() : 0;
		$this->category_id    = $ticket->category ? $ticket->category->getId() : 0;
		$this->priority_id    = $ticket->priority ? $ticket->priority->getId() : 0;
		$this->product_id     = $ticket->product ? $ticket->product->getId() : 0;
		$this->subject        = $ticket->subject;
	}

	public function offsetExists($offset)        { return (isset(self::$prop_names[$offset]) && isset($this->$offset)); }
    public function offsetGet($offset)           { if (isset(self::$prop_names[$offset])) return $this->$offset; }
    public function offsetSet($offset, $value)   { if (isset(self::$prop_names[$offset])) $this->$offset = $value; }
    public function offsetUnset($offset)         { if (isset(self::$prop_names[$offset])) $this->$offset = null; }
}
