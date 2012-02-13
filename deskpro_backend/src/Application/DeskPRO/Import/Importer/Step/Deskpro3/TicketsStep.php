<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketParticipant;

class TicketsStep extends AbstractDeskpro3Step
{
	/**
	 * @var array
	 */
	protected $custom_field_info = array();

	/**
	 * @var \Application\DeskPRO\CustomFields\FieldManager
	 */
	protected $fieldmanager;

	public static function getTitle()
	{
		return 'Import Tickets';
	}

	public function countPages()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM ticket");
		if (!$count) {
			return 1;
		}

		return ceil($count / 1000);
	}

	public function preRunAll()
	{
		$this->importer->removeTableIndexes('tickets');
		$this->importer->removeTableIndexes('ticket_message');
		$this->importer->removeTableIndexes('ticket_attachments');
		$this->importer->removeTableIndexes('ticket_participant');
		$this->importer->removeTableIndexes('custom_data_ticket');
	}

	public function postRunAll()
	{
		$this->importer->restoreTableIndexes('tickets');
		$this->importer->restoreTableIndexes('ticket_message');
		$this->importer->restoreTableIndexes('ticket_attachments');
		$this->importer->restoreTableIndexes('ticket_participant');
		$this->importer->restoreTableIndexes('custom_data_ticket');
	}

	public function run($page = 1)
	{
		if ($page == 1) {
			$this->preRunAll();
		}

		$this->custom_field_info = $this->getOldDb()->fetchAll("SELECT * FROM ticket_def");
		$this->fieldmanager = $this->getContainer()->getSystemService('ticket_fields_manager');

		$sub_start_time = microtime(true);
		$batch = $this->getIdsBatch($page - 1);
		$this->logMessage("-- Processing batch {$page}");

		try {
			$this->getDb()->beginTransaction();
			foreach ($batch as $tid) {
				$this->processTicket($tid);
			}
			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));

		if ($page >= $this->countPages()) {
			$this->postRunAll();
		}
	}


	/**
	 * Process a single ticket
	 * @param $ticket_id
	 */
	protected function processTicket($ticket_id)
	{
		#------------------------------
		# Make sure we havent already done it
		#------------------------------

		$check_exist = $this->getMappedNewId('ticket', $ticket_id);
		if ($check_exist) {
			$this->getLogger()->log("{$ticket_id} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Make the ticket
		#------------------------------

		$ticket_info = $this->getOldDb()->fetchAssoc("SELECT * FROM ticket WHERE id = ?", array($ticket_id));

		$new_person_id = $this->getMappedNewId('user', $ticket_info['userid']);
		$new_agent_id = null;
		if ($ticket_info['tech']) {
			$new_agent_id = $this->getMappedNewId('tech', $ticket_info['tech']);
		}

		if (!$new_person_id) {
			return;
		}

		$new_department_id = $this->getMappedNewId('ticket_category', $ticket_info['category']);
		if (!$new_department_id) {
			$new_department_id = $this->getDb()->fetchColumn("SELECT id FROM departments ORDER BY id ASC LIMIT 1");
		}

		$new_workflow_id = null;
		if ($ticket_info['workflow']) {
			$new_workflow_id = $this->getMappedNewId('ticket_workflow', $ticket_info['workflow']);
		}

		$new_priority_id = null;
		if ($ticket_info['priority']) {
			$new_priority_id = $this->getMappedNewId('ticket_priority', $ticket_info['priority']);
		}

		$new_org_id = null;
		if ($ticket_info['company']) {
			$new_org_id = $this->getMappedNewId('company', $ticket_info['company']);
		}

		$insert_ticket = array(
			'subject' => $ticket_info['subject'],
			'person_id' => $new_person_id,
			'agent_id' => $new_agent_id,
			'department_id' => $new_department_id,
			'workflow_id' => $new_workflow_id,
			'priority_id' => $new_priority_id,
			'organization_id' => $new_org_id,
			'ticket_hash' => sha1(microtime(true) . mt_rand(1000,99999)), // bogus hash
			'date_created' => date('Y-m-d H:i:s', $ticket_info['timestamp_opened']),
		);

		if ($ticket_info['creation'] == 'gateway') {
			$insert_ticket['creation_system'] = Ticket::CREATED_GATEWAY_PERSON;;
		} elseif ($ticket_info['creation'] == 'web' && !$ticket_info['tech_creator']) {
			$insert_ticket['creation_system'] = Ticket::CREATED_WEB_AGENT;
		} else {
			$insert_ticket['creation_system'] = Ticket::CREATED_WEB_PERSON;
		}

		if ($ticket_info['timestamp_closed']) {
			$insert_ticket['date_closed'] = date('Y-m-d H:i:s', $ticket_info['timestamp_closed']);
		}
		if ($ticket_info['timestamp_lastreply_user']) {
			$insert_ticket['date_last_user_reply'] = date('Y-m-d H:i:s', $ticket_info['timestamp_lastreply_user']);
		}
		if ($ticket_info['timestamp_lastreply_tech']) {
			$insert_ticket['date_last_agent_reply'] = date('Y-m-d H:i:s', $ticket_info['timestamp_lastreply_tech']);
		}
		if ($ticket_info['total_user_waiting']) {
			$insert_ticket['total_user_waiting'] = $ticket_info['total_user_waiting'];
		}
		if ($ticket_info['timestamp_tech_waiting']) {
			$insert_ticket['date_agent_waiting'] = date('Y-m-d H:i:s', $ticket_info['timestamp_tech_waiting']);
		}

		switch ($ticket_info['status']) {
			case 'awaiting_tech':
				$insert_ticket['status'] = Ticket::STATUS_AWAITING_AGENT;
				break;

			case 'awaiting_user':
				$insert_ticket['status'] = Ticket::STATUS_AWAITING_USER;
				break;

			case 'closed':
				$insert_ticket['status'] = Ticket::STATUS_RESOLVED;
				break;

			case 'nodisplay':
				$insert_ticket['status'] = Ticket::STATUS_HIDDEN;
				switch ($ticket_info['nodisplay']) {
					case 'spam':
						$insert_ticket['hidden_status'] = Ticket::HIDDEN_STATUS_SPAM;
						break;
					case 'validating':
						$insert_ticket['hidden_status'] = TIcket::HIDDEN_STATUS_VALIDATING;
						break;
				}
				break;
		}

		$this->getDb()->insert('tickets', $insert_ticket);
		$insert_ticket['id'] = $this->getDb()->lastInsertId();

		$this->saveMappedId('ticket', $ticket_id, $insert_ticket['id']);

		#------------------------------
		# Notes
		#------------------------------

		$ticket_notes = $this->getOldDb()->fetchAll("SELECT * FROM ticket_notes WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_notes as $note_info) {

			$pid = $this->getMappedNewId('tech', $note_info['techid']);
			if (!$pid) {
				continue;
			}

			$insert_message = array();
			$insert_message['message_hash'] = sha1(microtime(true) . mt_rand(1000,99999)); // bogus hash
			$insert_message['message'] = nl2br(htmlspecialchars($note_info['note'], \ENT_QUOTES));
			$insert_message['person_id'] = $pid;
			$insert_message['ticket_id'] = $insert_ticket['id'];
			$insert_message['is_agent_note'] = 1;
			$insert_message['creation_system'] = 'web';
			$insert_message['date_created'] = date('Y-m-d H:i:s', $note_info['timestamp']);

			$this->getDb()->insert('tickets_messages', $insert_message);
		}

		#------------------------------
		# Messages
		#------------------------------

		$ticket_messages = $this->getOldDb()->fetchAll("SELECT * FROM ticket_message WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_messages as $message_info) {

			if ($message_info['techid']) {
				$pid = $this->getMappedNewId('tech', $message_info['techid']);
			} else {
				$pid = $this->getMappedNewId('user', $message_info['userid']);
			}
			if (!$pid) {
				continue;
			}

			$insert_message = array();
			$insert_message['message_hash'] = sha1(microtime(true) . mt_rand(1000,99999)); // bogus hash
			$insert_message['message'] = nl2br(htmlspecialchars($message_info['message'], \ENT_QUOTES));
			$insert_message['person_id'] = $pid;
			$insert_message['ticket_id'] = $insert_ticket['id'];
			$insert_message['is_agent_note'] = 1;
			$insert_message['creation_system'] = 'web';
			$insert_message['date_created'] = date('Y-m-d H:i:s', $message_info['timestamp']);
			$insert_message['ip_address'] = $message_info['ipaddress'];

			if ($message_info['charset'] && strtoupper($message_info['charset']) != 'UTF-8') {

				// Fix common missing charsets
				if (strtoupper($message_info['charset']) == 'US-ASCII' || !trim($message_info['charset'])) {
					$message_info['charset'] = 'ISO-8859-1';
				}

				// Fix charsets with a country prepended like en_US.ISO-8859-1
				if (strpos($message_info['charset'], '.')) {
					$parts = explode('.', $message_info['charset'], 2);
					$message_info['charset'] = $parts[1];
				}

				// Surrounded in curlies like {windows-1251} (why? dont ask me)
				if (preg_match('#^\{(.*?)\}$#', $message_info['charset'], $m)) {
					$message_info['charset'] = $m[1];
				}

				if (!preg_match('#^[a-zA-Z0-9\-]+$#', $message_info['charset'])) {
					$message_info['charset'] = 'ISO-8859-1';
				}

				$new_msg = @iconv($message_info['charset'], 'UTF-8//IGNORE//TRANSLIT', $message_info['message']);
				if ($new_msg) {
					$message_info['message'] = $new_msg;
				}
			}

			$insert_message['message'] = $message_info['message'];

			$this->getDb()->insert('tickets_messages', $insert_message);
			$insert_message['id'] = $this->getDb()->lastInsertId();

			$this->saveMappedId('ticket_message', $message_info['id'], $insert_message['id']);
		}

		#------------------------------
		# Attachments
		#------------------------------

		$ticket_attachments = $this->getOldDb()->fetchAll("SELECT * FROM ticket_attachments WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_attachments as $attach_info) {

			if ($attach_info['techid']) {
				$pid = $this->getMappedNewId('tech', $attach_info['techid']);
			} else {
				$pid = $this->getMappedNewId('user', $attach_info['userid']);
			}
			if (!$pid) {
				continue;
			}

			$blob_id = $this->getMappedNewId('blob', $attach_info['blobid']);
			if (!$blob_id) {
				continue;
			}

			$insert_attach = array();
			$insert_attach['ticket_id'] = $insert_ticket['id'];
			$insert_attach['person_id'] = $pid;

			if ($attach_info['messageid']) {
				$insert_attach['message_id'] = $this->getMappedNewId('ticket_message', $attach_info['messageid']);
			}

			$insert_attach['blob_id'] = $blob_id;

			$this->getDb()->insert('tickets_attachments', $insert_attach);
		}

		#------------------------------
		# Participants
		#------------------------------

		$ticket_parts = $this->getOldDb()->fetchAll("SELECT * FROM ticket_participant WHERE ticket = ?", array($ticket_info['id']));
		foreach ($ticket_parts as $part_info) {
			if ($part_info['user_type'] == 'tech') {
				$pid = $this->getMappedNewId('tech', $part_info['user']);
			} else {
				$pid = $this->getMappedNewId('user', $part_info['user']);
			}

			if (!$pid) {
				continue;
			}

			$insert_tac = array();
			$insert_tac['auth'] = \Orb\Util\Strings::random(6, \Orb\Util\Strings::CHARS_KEY);
			$insert_tac['person_id'] = $pid;
			$insert_tac['ticket_id'] = $insert_ticket['id'];
			$this->getDb()->insert('ticket_access_codes', $insert_tac);
			$insert_tac['id'] = $this->getDb()->lastInsertId();

			$insert_part = array();
			$insert_part['person_id'] = $pid;
			$insert_part['ticket_id'] = $insert_ticket['id'];
			$insert_part['access_code_id'] = $insert_tac['id'];
			$this->getDb()->insert('tickets_participants', $insert_part);
		}

		#------------------------------
		// Custom fields
		#------------------------------

		foreach ($this->custom_field_info as $field_info) {
			$name = $field_info['name'];
			if (!isset($ticket_info[$name]) || !$ticket_info[$name]) {
				continue;
			}

			$field = $this->fieldmanager->getFieldFromId($this->getMappedNewId('ticket_def', $field_info['id']));
			if (!$field) {
				continue;
			}

			$data = null;
			switch ($field->handler_class) {
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
					$this->getDb()->insert('custom_data_ticket', array(
						'ticket_id' => $insert_ticket['id'],
						'field_id' => $field->id,
						'input' => $user_info[$name]
					));
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
					$val = str_replace('|||', '', $user_info[$name]);
					$new_val = $this->getMappedNewId('ticket_def_choice', $val);
					if ($new_val) {
						$this->getDb()->insert('custom_data_ticket', array(
							'ticket_id' => $insert_ticket['id'],
							'field_id' => $new_val,
							'value' => 1
						));
					}
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMulti':
					$vals = explode('|||', $user_info[$name]);
					foreach ($vals as $val) {
						$new_val = $this->getMappedNewId('ticket_def_choice', $val);
						if ($new_val) {
							$this->getDb()->insert('custom_data_person', array(
								'ticket_id' => $insert_ticket['id'],
								'field_id' => $new_val,
								'value' => 1
							));
						}
					}
					break;
			}
		}
	}

	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($page)
	{
		$start = $page * 1000;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM ticket ORDER BY id ASC LIMIT $start, 1000");

		return $ids;
	}
}
