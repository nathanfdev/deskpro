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
	 * @var array
	 */
	private  $row_data = null;

	/**
	 * @var string
	 */
	private  $search_text = null;

	public function __construct(Connection $db, Ticket $ticket)
	{
		$this->db = $db;
		$this->ticket = $ticket;
	}


	/**
	 * Return the raw ticket row directly from the database
	 *
	 * @return array
	 */
	public function getRowData()
	{
		if ($this->row_data !== null) {
			return $this->row_data;
		}

		$this->row_data = $this->ticket->getDbRow();

		return $this->row_data;
	}


	/**
	 * Remove the ticket from search tables
	 */
	public function remove()
	{
		$this->db->delete('tickets_search_active',         array('id' => $this->ticket->getOriginalId()));
		$this->db->delete('tickets_search_message_active', array('id' => $this->ticket->getOriginalId()));
		$this->db->delete('tickets_search_message',        array('id' => $this->ticket->getOriginalId()));
		$this->db->delete('tickets_search_subject',        array('id' => $this->ticket->getOriginalId()));
	}

	/**
	 * Update or add ticket to search tables
	 */
	public function update()
	{
		$clone_data_search = $this->getCloneData(true);
		$clone_data = $this->getCloneData(false);

		$this->db->replace('tickets_search_message', $clone_data_search);
		$this->db->replace('tickets_search_subject', array(
			'id'      => $this->ticket->id,
			'subject' => $this->ticket->subject
		));

		if (!$this->ticket->isArchived()) {
			$this->db->replace('tickets_search_active', $clone_data);
			$this->db->replace('tickets_search_message_active', $clone_data_search);
		} else {
			$this->db->delete('tickets_search_active', array('id' => $this->ticket->id));
			$this->db->delete('tickets_search_message_active', array('id' => $this->ticket->id));
		}
	}

	/**
	 * @return array
	 */
	public function getCloneData($with_search_content = false)
	{
		$set_data = array();
		$row_data = $this->getRowData();
		foreach ($this->getCloneFields() as $k) {
			$set_data[$k] = $row_data[$k];
		}

		if ($with_search_content) {
			$set_data['content'] = $this->getSearchContent();
		}

		return $set_data;
	}


	/**
	 * @return string
	 */
	public function getSearchContent()
	{
		if ($this->search_text !== null) {
			return $this->search_text;
		}

		$this->search_text = $this->db->fetchAllCol("
			SELECT message
			FROM tickets_messages
			WHERE ticket_id = ?
		", array($this->ticket->id));

		$this->search_text = implode(' ', $this->search_text);

		return $this->search_text;
	}


	/**
	 * @return array
	 */
	public function getCloneFields()
	{
		return array(
			'id', 'language_id', 'department_id', 'category_id', 'priority_id', 'workflow_id', 'product_id', 'person_id', 'agent_id',
			'agent_team_id', 'organization_id', 'email_gateway_id', 'creation_system', 'status', 'urgency', 'is_hold', 'date_created', 'date_resolved', 'date_first_agent_reply',
			'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'total_user_waiting', 'total_to_first_reply',
		);
	}
}