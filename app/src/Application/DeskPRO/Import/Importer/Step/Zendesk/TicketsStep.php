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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Zendesk;

use Orb\Service\Zendesk\ApiException;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class TicketsStep extends AbstractZendeskStep
{
	const PERPAGE = 100;

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
		$res = $this->zd->sendGet('tickets', array('per_page' => 1));
		$count = (int)$res->get('count');

		$this->logMessage(sprintf("%d records in %d pages", $count, ceil($count / self::PERPAGE)));

		return ceil($count / self::PERPAGE);
	}

	public function run($page = 1)
	{
		$sub_start_time = microtime(true);
		$this->logMessage("-- Processing batch {$page}");

		$this->fieldmanager = $this->getContainer()->getSystemService('ticket_fields_manager');

		$tickets = $this->getBatch($page);

		$this->db->beginTransaction();
		try {
			foreach ($tickets as $t) {
				$this->processTicket($t);
			}
			$this->importer->flushSaveMappedIdBuffer();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
	}


	protected function processTicket($ticket_info)
	{
		$ticket_id = $ticket_info['id'];

		if ($this->getMappedNewId('zd_ticket_id', $ticket_id)) {
			// Already imported (skip)
			return;
		}

		if (!$this->getMappedNewId('zd_user_id', $ticket_info['requester_id'])) {
			return;
		}

		$search_content = array();
		$search_content[] = $ticket_info['subject'];

		try {
			$ticket_metrics = $this->zd->sendGet("tickets/{$ticket_info['id']}/metrics");
		} catch (ApiException $e) {
			$ticket_metrics = new OptionsArray();
			$this->logMessage(sprintf("Ticket %d has no metrics data", $ticket_id));
		}

		#------------------------------
		# Create the ticket
		#------------------------------

		$insert_ticket = array();
		$insert_ticket['id']            = $ticket_id;
		$insert_ticket['date_created']  = date('Y-m-d H:i:s', strtotime($ticket_info['created_at']));
		$insert_ticket['person_id']     = $this->getMappedNewId('zd_user_id', $ticket_info['requester_id']);
		$insert_ticket['subject']       = $ticket_info['subject'];
		$insert_ticket['ref']           = 'TICKET-' . $ticket_id;
		$insert_ticket['department_id'] = $this->getMappedNewId('zd_groupdep_id', $ticket_info['group_id']) ?: null;

		switch ($ticket_info['status']) {
			case 'new':
			case 'open':
			case 'hold':
				$insert_ticket['status'] = 'awaiting_agent';

				if ($ticket_info['status'] == 'hold') {
					$insert_ticket['is_hold'] = 1;
				}
				break;

			case 'pending':
				$insert_ticket['status'] = 'awaiting_user';
				break;

			case 'solved':
			case 'closed':
				$insert_ticket['status'] = 'resolved';
				break;
		}

		if ($ticket_info['organization_id'] && $this->getMappedNewId('zd_org_id', $ticket_info['organization_id'])) {
			$insert_ticket['organization_id'] = $this->getMappedNewId('zd_org_id', $ticket_info['organization_id']);
		}
		if ($ticket_info['assignee_id'] && $this->getMappedNewId('zd_user_id', $ticket_info['assignee_id'])) {
			$insert_ticket['agent_id'] = $this->getMappedNewId('zd_user_id', $ticket_info['assignee_id']);
		}
		if ($ticket_info['group_id'] && $this->getMappedNewId('zd_group_id', $ticket_info['group_id'])) {
			$insert_ticket['agent_team_id'] = $this->getMappedNewId('zd_group_id', $ticket_info['group_id']);
		}

		if ($ticket_info['priority']) {
			switch ($ticket_info['priority']) {
				case 'low':
					$insert_ticket['priority_id'] = 1;
					break;
				case 'normal':
					$insert_ticket['priority_id'] = 2;
					break;
				case 'high':
					$insert_ticket['priority_id'] = 3;
					break;
				case 'urgent':
					$insert_ticket['priority_id'] = 4;
					break;
			}
		}

		$insert_ticket['date_first_agent_reply'] = null;
		$insert_ticket['date_last_agent_reply']  = null;
		$insert_ticket['date_last_user_reply']   = null;
		$insert_ticket['date_agent_waiting']     = null;
		$insert_ticket['date_user_waiting']      = null;
		$insert_ticket['total_user_waiting']     = 0;
		$insert_ticket['total_to_first_reply']   = 0;

		if ($ticket_metrics->get('reply_time_in_minutes')) {
			$insert_ticket['total_to_first_reply'] = $ticket_metrics['reply_time_in_minutes'] * 60;
			$insert_ticket['date_first_agent_reply'] = date('Y-m-d H:i:s', strtotime($ticket_metrics['assignee_updated_at']) + $insert_ticket['total_to_first_reply']);
		}

		if ($ticket_metrics->get('requester_wait_time_in_minutes')) {
			$insert_ticket['total_user_waiting'] = $ticket_metrics['requester_wait_time_in_minutes'] * 60;
		}

		if ($ticket_metrics->get('assignee_updated_at')) {
			$insert_ticket['date_last_agent_reply'] = date('Y-m-d H:i:s', strtotime($ticket_metrics['assignee_updated_at']));
		}

		if ($ticket_metrics->get('requester_updated_at')) {
			$insert_ticket['date_last_user_reply'] = date('Y-m-d H:i:s', strtotime($ticket_metrics['requester_updated_at']));
		}

		if ($ticket_metrics->get('status_updated_at')) {
			$date = date('Y-m-d H:i:s', strtotime($ticket_metrics['status_updated_at']));

			$insert_ticket['date_status'] = $date;

			if ($insert_ticket['status'] == 'awaiting_user') {
				$insert_ticket['date_agent_waiting'] = $date;
			} elseif ($insert_ticket['status'] == 'awaiting_agent') {
				$insert_ticket['date_user_waiting'] = $date;
			}
		}

		$this->db->insert('tickets', $insert_ticket);
		$this->saveMappedId('zd_ticekt_id', $insert_ticket['id'], $ticket_id);

		#------------------------------
		# Insert labels
		#------------------------------

		if ($ticket_info['tags']) {
			$insert_bulk = array();
			foreach ($ticket_info['tags'] as $tag) {
				$row = array();
				$row['ticket_id'] = $ticket_id;
				$row['label'] = strtolower($tag);

				$search_content[] = md5("lbl" . md5(strtolower(trim($tag))));

				$insert_bulk[] = $row;
			}

			$this->db->batchInsert('labels_tickets', $insert_bulk, true);
		}

		#------------------------------
		# Custom fields
		#------------------------------

		if (!empty($ticket_info['custom_fields'])) {
			$insert_field_data = array();

			$all_field_info = Arrays::keyFromData($ticket_info['custom_fields'], 'id', 'value');

			foreach ($all_field_info as $old_field_id => $field_val) {

				if ($field_val === null) {
					continue;
				}

				$field_id = $this->getMappedNewId('zd_ticket_field_id', $old_field_id);

				$field = $this->fieldmanager->getFieldFromId($field_id);
				if (!$field) {
					continue;
				}

				switch ($field->handler_class) {
					case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
					case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
						$insert_field_data[] = array(
							'ticket_id'     => $ticket_id,
							'field_id'      => $field_id,
							'root_field_id' => $field_id,
							'value'         => 0,
							'input'         => $field_val
						);
						break;

					case 'Application\\DeskPRO\\CustomFields\\Handler\\ToggleField':
						$insert_field_data[] = array(
							'ticket_id'     => $ticket_id,
							'field_id'      => $field_id,
							'root_field_id' => $field_id,
							'value'         => 1,
							'input'         => ''
						);
						break;

					case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':

						$sub_field_id = $this->getMappedNewId('zd_tagger_id', $field_id . '_' . $field_val);
						if (!$sub_field_id) {
							continue;
						}

						$insert_field_data[] = array(
							'ticket_id'     => $ticket_id,
							'field_id'      => $sub_field_id,
							'root_field_id' => $field_id,
							'value'         => 1,
							'input'         => ''
						);
						break;
				}
			}

			if ($insert_field_data) {
				$this->db->batchInsert('custom_data_ticket', $insert_field_data);
			}
		}

		#------------------------------
		# Get reply/log/note data
		#------------------------------

		$add_logs      = array();
		$add_datastore = array();

		$add_logs[] = array(
			'ticket_id'    => $ticket_id,
			'action_type'  => 'free',
			'date_created' => date('Y-m-d H:i:s'),
			'details'      => serialize(array(
				'message'  => 'Ticket imported',
			))
		);

		$audits_raw = $this->zd->sendGetAll("tickets/$ticket_id/audits", 'audits', array('per_page' => 100));
		$audits = array();

		// Format into a "flat" structure
		foreach ($audits_raw as $audit) {
			if (empty($audit['events'])) {
				continue;
			}

			foreach ($audit['events'] as $event) {
				$line = $event;
				$line['via'] = $audit['via'];

				if (empty($line['author_id']) || !$line['author_id']) {
					$line['author_id'] = $audit['author_id'];
				}
				if (empty($line['created_at']) || !$line['created_at']) {
					$line['created_at'] = $audit['created_at'];
				}
				if (empty($line['metadata']) || !$line['metadata']) {
					$line['metadata'] = $audit['metadata'];
				}

				$audits[] = $line;
			}
		}

		foreach ($audits as $line) {
			switch ($line['type']) {
				case 'Comment':
					$add_message = array(
						'ticket_id'       => $ticket_id,
						'person_id'       => $this->getMappedNewId('zd_user_id', $line['author_id']) ?: $insert_ticket['person_id'],
						'date_created'    => date('Y-m-d H:i:s', strtotime($line['created_at'])),
						'is_agent_note'   => $line['public'] ? 0 : 1,
						'creation_system' => 'web',
						'message_hash'    => sha1(microtime(true) . mt_rand(1000,99999)), // bogus hash
						'message'         => $line['html_body'],
					);

					$this->db->insert('tickets_messages', $add_message);
					$message_id =  $this->db->lastInsertId();

					$search_content[] = $line['body'];

					if (!empty($line['attachments'])) {
						foreach ($line['attachments'] as $attach) {
							$add_datastore[] = array(
								'typename' => 'attach.ticket.' . uniqid('t'.$ticket_id),
								'data'     => serialize(array(
									'type'          => 'ticket',
									'ticket_id'     => $ticket_id,
									'message_id'    => $message_id,
									'person_id'     => $this->getMappedNewId('zd_user_id', $line['author_id']) ?: $insert_ticket['person_id'],
									'is_agent_note' => $line['public'] ? 0 : 1,
									'url'           => $attach['content_url'],
									'filename'      => $attach['file_name'],
									'filesize'      => $attach['size'],
									'content_type'  => $attach['content_type'],
								))
							);
						}
					}
					break;
			}
		}

		if ($add_logs) {
			$this->db->batchInsert('tickets_logs', $add_logs);
		}
		if ($add_datastore) {
			$this->db->batchInsert('import_datastore', $add_datastore);
		}

		#------------------------------
		# Search Tables
		#------------------------------

		$search_content = implode(' ', $search_content);

		$this->db->replace('content_search', array(
			'object_type' => 'ticket',
			'object_id'   => $ticket_id,
			'content'     => $search_content,
		));

		$fields = array(
			'id', 'department_id', 'priority_id', 'person_id', 'agent_id',
			'agent_team_id', 'organization_id', 'creation_system', 'status', 'is_hold', 'date_created', 'date_first_agent_reply',
			'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'total_user_waiting', 'total_to_first_reply',
		);

		$set_data = array();
		foreach ($fields as $k) {
			if (isset($insert_ticket[$k])) {
				$set_data[$k] = $insert_ticket[$k];
			}
		}

		$set_data_content = $set_data;
		$set_data_content['content'] = $search_content;

		$this->db->replace('tickets_search_message', $set_data_content);
		$this->db->replace('tickets_search_subject', array(
			'id' => $ticket_id,
			'subject' => $insert_ticket['subject']
		));

		if ($insert_ticket['status'] != 'closed' && $insert_ticket['status'] != 'hidden') {
			$this->db->replace('tickets_search_active', $set_data);
			$this->db->replace('tickets_search_message_active', $set_data_content);
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getBatch($page)
	{
		$this->logMessage(sprintf("Getting batch of %d (page %d)", self::PERPAGE, $page));
		$t = microtime(true);

		$res = $this->zd->sendGet('tickets', array('per_page' => self::PERPAGE, 'page' => $page));
		$this->logMessage(sprintf("-- Call took %.4f seconds", microtime(true) - $t));

		$batch = $res->get('tickets');

		return $batch;
	}
}
