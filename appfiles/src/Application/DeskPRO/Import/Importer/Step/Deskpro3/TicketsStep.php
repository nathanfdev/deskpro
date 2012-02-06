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

	public function run($page = 1)
	{
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

		$new_person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $ticket_info['userid']));
		$new_agent = null;
		if ($ticket_info['tech']) {
			$new_agent = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $ticket_info['tech']));
		}

		if (!$new_person) {
			return;
		}

		$new_department = null;
		if ($ticket_info['category']) {
			$new_department = $this->getEm()->find('DeskPRO:Department', $this->getMappedNewId('ticket_category', $ticket_info['category']));
		}

		$new_workflow = null;
		if ($ticket_info['workflow']) {
			$new_workflow = $this->getEm()->find('DeskPRO:TicketWorkflow', $this->getMappedNewId('ticket_workflow', $ticket_info['workflow']));
		}

		$new_priority = null;
		if ($ticket_info['priority']) {
			$new_priority = $this->getEm()->find('DeskPRO:TicketPriority', $this->getMappedNewId('ticket_priority', $ticket_info['priority']));
		}

		$new_org = null;
		if ($ticket_info['company']) {
			$new_org = $this->getEm()->find('DeskPRO:Organization', $this->getMappedNewId('company', $ticket_info['company']));
		}

		$ticket = new Ticket();
		$ticket->setNoLog(); // dont want the change logger to happen for all these tickets
		$ticket->subject       = $ticket_info['subject'];
		$ticket->person        = $new_person;
		$ticket->agent         = $new_agent;
		$ticket->department    = $new_department;
		$ticket->workflow      = $new_workflow;
		$ticket->priority      = $new_priority;
		$ticket->organization  = $new_org;
		$ticket->ticket_hash  = sha1(microtime(true) . mt_rand(1000,99999)); // bogus hash
		$ticket->date_createad = new \DateTime('@' . $ticket_info['timestamp_opened']);

		if ($ticket_info['creation'] == 'gateway') {
			$ticket->creation_system = Ticket::CREATED_GATEWAY_PERSON;
		} elseif ($ticket_info['creation'] == 'web' && !$ticket_info['tech_creator']) {
			$ticket->creation_system = Ticket::CREATED_WEB_AGENT;
		} else {
			$ticket->creation_system = Ticket::CREATED_WEB_PERSON;
		}

		if ($ticket_info['timestamp_closed']) {
			$ticket->date_closed = new \DateTime('@' . $ticket_info['timestamp_closed']);
		}
		if ($ticket_info['timestamp_lastreply_user']) {
			$ticket->date_last_user_reply = new \DateTime('@' . $ticket_info['timestamp_lastreply_user']);
		}
		if ($ticket_info['timestamp_lastreply_tech']) {
			$ticket->date_last_agent_reply = new \DateTime('@' . $ticket_info['timestamp_lastreply_tech']);
		}
		if ($ticket_info['total_user_waiting']) {
			$ticket->total_user_waiting = $ticket_info['total_user_waiting'];
		}
		if ($ticket_info['timestamp_tech_waiting']) {
			$ticket->date_agent_waiting = new \DateTime('@' . $ticket_info['timestamp_tech_waiting']);
		}

		switch ($ticket_info['status']) {
			case 'awaiting_tech':
				$ticket->status = Ticket::STATUS_AWAITING_AGENT;
				break;

			case 'awaiting_user':
				$ticket->status = Ticket::STATUS_AWAITING_USER;
				break;

			case 'closed':
				$ticket->status = Ticket::STATUS_RESOLVED;
				break;

			case 'nodisplay':
				$ticket->status = Ticket::STATUS_HIDDEN;
				switch ($ticket_info['nodisplay']) {
					case 'spam':
						$ticket->hidden_status = Ticket::HIDDEN_STATUS_SPAM;
						break;
					case 'validating':
						$ticket->hidden_status = TIcket::HIDDEN_STATUS_VALIDATING;
						break;
				}
				break;
		}

		$this->getEm()->persist($ticket);
		$this->getEm()->flush();

		#------------------------------
		# Notes
		#------------------------------

		$ticket_notes = $this->getOldDb()->fetchAll("SELECT * FROM ticket_notes WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_notes as $note_info) {
			$message = new TicketMessage();
			$message->message_hash = sha1(microtime(true) . mt_rand(1000,99999)); // bogus hash
			$p = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $note_info['techid']));
			if (!$p) {
				continue;
			}
			$message->person = $p;
			$message->message = $note_info['note'];
			$message->ticket = $ticket;
			$message->is_agent_note = true;

			$this->getEm()->persist($message);
			$this->getEm()->flush();

			$this->saveMappedId('ticket_note', $note_info['id'], $message->id);
		}

		#------------------------------
		# Messages
		#------------------------------

		$ticket_messages = $this->getOldDb()->fetchAll("SELECT * FROM ticket_message WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_messages as $message_info) {
			$message = new TicketMessage();
			$message->message_hash = sha1(microtime(true) . mt_rand(1000,99999)); // bogus hash
			$message->message = $message_info['message'];
			$message->ticket = $ticket;

			if ($message_info['techid']) {
				$message->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $message_info['techid']));
			} else {
				$message->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $message_info['userid']));
			}

			if (!$message->person) {
				continue;
			}

			$message->ip_address = $message_info['ipaddress'];

			if ($message_info['charset'] && $message_info['charset'] != 'utf8') {
				$new_msg = @iconv($message_info['charset'], 'UTF-8//TRANSLIT', $message_info['message']);
				if ($new_msg) {
					$message_info['message'] = $new_msg;
				}
			}

			$this->getEm()->persist($message);
			$this->getEm()->flush();

			$this->saveMappedId('ticket_message', $message_info['id'], $message->id);
		}

		#------------------------------
		# Attachments
		#------------------------------

		$ticket_attachments = $this->getOldDb()->fetchAll("SELECT * FROM ticket_attachments WHERE ticketid = ?", array($ticket_info['id']));
		foreach ($ticket_attachments as $attach_info) {
			$attach = new TicketAttachment();
			$attach->ticket = $ticket;

			if ($attach_info['techid']) {
				$attach->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $attach_info['techid']));
			} elseif ($attach_info['userid']) {
				$attach->person = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $attach_info['userid']));
			}

			if ($attach_info['messageid']) {
				$attach->message = $this->getEm()->find('DeskPRO:TicketMessage', $this->getMappedNewId('ticket_message', $attach_info['messageid']));
			}

			$attach->blob = $this->getEm()->find('DeskPRO:Blob', $this->getMappedNewId('blob', $attach_info['blobid']));

			$this->getEm()->persist($attach);
			$this->getEm()->flush();
		}

		#------------------------------
		# Participants
		#------------------------------

		$ticket_parts = $this->getOldDb()->fetchAll("SELECT * FROM ticket_participant WHERE ticket = ?", array($ticket_info['id']));
		foreach ($ticket_parts as $part_info) {
			if ($part_info['user_type'] == 'tech') {
				$p = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('tech', $part_info['user']));
			} else {
				$p = $this->getEm()->find('DeskPRO:Person', $this->getMappedNewId('user', $part_info['user']));
			}

			if (!$p) {
				continue;
			}

			$part = new TicketParticipant();
			$part->person = $p;
			$part->ticket = $ticket;

			$this->getEm()->persist($part);
			$this->getEm()->flush();
		}

		#------------------------------
		// Custom fields
		#------------------------------

		$form_data = array();
		foreach ($this->custom_field_info as $field_info) {
			$name = $field_info['name'];
			if (!isset($ticket_info[$name]) || !$ticket_info[$name]) {
				continue;
			}

			$field = $this->getEm()->find('DeskPRO:CustomDefTicket', $this->getMappedNewId('ticket_def', $field_info['id']));
			if (!$field) {
				continue;
			}

			$data = null;
			switch ($field->handler_class) {
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
					$data = $ticket_info[$name];
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
					$val = str_replace('|||', '', $ticket_info[$name]);
					$new_val = $this->getMappedNewId('ticket_def_choice', $val);
					if ($new_val) {
						$data = $new_val;
					}
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMulti':
					$vals = explode('|||', $ticket_info[$name]);
					$new_vals = array();
					foreach ($vals as $val) {
						$new_val = $this->getMappedNewId('ticket_def_choice', $val);
						if ($new_val) {
							$new_vals[] = $new_val;
						}
					}
					if ($new_vals) {
						$data = $new_vals;
					}
					break;
			}

			if ($data) {
				$form_data['field_' . $field->id] = $data;
			}
		}

		$this->getEm()->persist($ticket);
		$this->getEm()->flush();
		$this->saveMappedId('ticket', $ticket_id, $ticket->id);

		if ($form_data) {
			// TODO fix custom field saving
			//$this->fieldmanager->saveFormToObject($form_data, $ticket);
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
