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

namespace Application\DeskPRO\Tickets\EditTicket;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class EditTicket implements \Application\DeskPRO\People\PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\PersonProps
	 */
	public $person;

	/**
	 * The person who is running this (ex an agent?)
	 */
	protected $person_context;

	/**
	 * The actual ticket
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket_object;

	/**
	 * @var \Application\DeskPRO\Tickets\NewTicket\TicketProps
	 */
	public $ticket;

	public $custom_ticket_fields = array();

	/**
	 * @var array
	 */
	protected $display_fields = array();

	public function setPageData($page_data)
	{
		foreach ($page_data as $i) {
			$this->display_fields[$i['id']] = $i['id'];
		}
	}

	public function __construct(Ticket $ticket_object)
	{
		$this->ticket_object = $ticket_object;
		$this->ticket = new EditTicketProps($ticket_object);

		for ($i = 0; $i < 500; $i++) {
			$this->custom_ticket_fields["field_$i"] = null;
		}
	}

	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}

	public function save()
	{
		App::getDb()->beginTransaction();
		try {
			if (isset($this->display_fields['ticket_department'])) {
				$this->ticket_object->department = $this->ticket->department_id ? App::findEntity('DeskPRO:Department', $this->ticket->department_id) : null;
			}
			if (isset($this->display_fields['ticket_category'])) {
				$this->ticket_object->category   = $this->ticket->department_id ? App::findEntity('DeskPRO:TicketCategory', $this->ticket->category_id) : null;
			}
			if (isset($this->display_fields['ticket_priority'])) {
				$this->ticket_object->priority   = $this->ticket->department_id ? App::findEntity('DeskPRO:TicketPriority', $this->ticket->priority_id) : null;
			}
			if (isset($this->display_fields['ticket_product'])) {
				$this->ticket_object->product    = $this->ticket->department_id ? App::findEntity('DeskPRO:Product', $this->ticket->product_id) : null;
			}

			$field_manager = App::getSystemService('ticket_fields_manager');
			$post_custom_fields = App::getRequest()->request->get('custom_fields', array());
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $this->ticket_object);
			}

			App::getOrm()->persist($this->ticket_object);
			App::getOrm()->flush();

			App::getDb()->commit();
		} catch (\Exception $e) {
			App::getDb()->rollback();
			throw $e;
		}
	}
}
