<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\UserBundle\Tickets\EditTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

/**
 * This wraps up the 'ticket' data of a newticket
 */
class TicketProps
{
	public $subject = '';

	public $department_id = 0;
	public $category_id   = 0;
	public $priority_id   = 0;
	public $product_id    = 0;

	public $custom_fields = array();

	public function __construct(Ticket $ticket)
	{
		$this->subject       = $ticket['subject'];
		$this->department_id = $ticket['department_id'];
		$this->category_id   = $ticket['category_id'];
		$this->priority_id   = $ticket['priority_id'];
		$this->product_id    = $ticket['product_id'];

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($ticket['custom_data'], $ticket_field_defs);

		foreach ($ticket_data_structured as $k => $v) {
			$this->custom_fields[$k] = $v;
		}
	}
}
