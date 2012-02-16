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