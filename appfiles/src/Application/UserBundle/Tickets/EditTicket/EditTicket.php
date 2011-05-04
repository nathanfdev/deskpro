<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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