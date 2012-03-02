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
 * New ticket acts as the processor and domain object for a newticket form
 */
class EditTicket
{
	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\TicketProps
	 */
	public $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	public $real_ticket;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = new TicketProps($ticket);
		$this->real_ticket = $ticket;
	}

	public function save()
	{
		$this->subject       = $ticket['subject'];
		$this->department_id = $ticket['department_id'];
		$this->category_id   = $ticket['category_id'];
		$this->priority_id   = $ticket['priority_id'];
		$this->product_id    = $ticket['product_id'];

		$this->real_ticket['subject']        = $this->ticket['subject'];
		$this->real_ticket['department_id']  = $this->ticket['department_id'];
		$this->real_ticket['category_id']    = $this->ticket['category_id'];
		$this->real_ticket['priority_id']    = $this->ticket['priority_id'];
		$this->real_ticket['product_id']     = $this->ticket['product_id'];

		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		foreach ($ticket_field_defs as $field_def) {
			foreach ($field_def->getHandler()->getDataFromForm($this->ticket->custom_fields) as $info) {
				$this->real_ticket->setCustomData($info[0], $info[1], $info[2]);
			}
		}

		App::getOrm()->beginTransaction();
		App::getOrm()->persist($this->real_ticket);
		App::getOrm()->flush();
	}
}
