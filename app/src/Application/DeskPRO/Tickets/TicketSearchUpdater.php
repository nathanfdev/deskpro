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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSearchActive;

/**
 * Updates the archive tables
 */
class TicketSearchUpdater
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * Cols from TicketSearchActive::getFieldNames. Defined here just as a micro-opt.
	 * @var string
	 */
	private static $cols = "
		`id`, `ref`, `language_id`, `department_id`, `category_id`, `workflow_id`, `priority_id`, `product_id`,
		`person_id`, `person_email_id`, `agent_id`, `agent_team_id`, `organization_id`, `creation_system`,
		`creation_system_option`, `status`, `is_hold`, `urgency`, `feedback_rating`, `date_feedback_rating`,
		`date_created`, `date_resolved`, `date_first_agent_assign`, `date_first_agent_reply`, `date_last_agent_reply`,
		`date_last_user_reply`, `date_agent_waiting`, `date_user_waiting`, `date_status`, `total_user_waiting`,
		`total_to_first_reply`, `subject`, `original_subject`
	";

	public function __construct(Connection $db, Ticket $ticket)
	{
		$this->db = $db;
		$this->ticket = $ticket;
	}

	/**
	 * Remove the ticket from search tables
	 */
	public function remove()
	{
		$this->db->delete('tickets_search_active', array('id' => $this->ticket->getOriginalId()));
	}

	/**
	 * Update or add ticket to search tables
	 */
	public function update()
	{
		if (!$this->ticket->isArchived()) {
			$cols = self::$cols;
			$data = $this->db->fetchAssoc("SELECT {$cols} FROM tickets WHERE id = ?", array($this->ticket->id));
			if ($data && $data['status'] != 'closed') {
				$this->db->replace('tickets_search_active', $data);
			} else {
				$this->db->delete('tickets_search_active', array('id' => $this->ticket->id));
			}
		} else {
			$this->db->delete('tickets_search_active', array('id' => $this->ticket->id));
		}
	}
}