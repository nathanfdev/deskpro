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

class TicketsStep extends AbstractZendeskStep
{
	const PERPAGE = 100;

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

		$this->db->exec('DELETE FROM tickets');

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

		if (!$this->verifyUserId($ticket_info['requester_id'])) {
			return;
		}

		$insert_ticket = array();
		$insert_ticket['id']           = $ticket_id;
		$insert_ticket['date_created'] = date('Y-m-d H:i:s', strtotime($ticket_info['created_at']));
		$insert_ticket['person_id']    = $ticket_info['requeser_id'];
		$insert_ticket['subject']      = $ticket_info['subject'];

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
				$insert_ticket['status'] = 'resolved';
				break;

			case 'closed':
				$insert_ticket['status'] = 'closed';
				break;
		}

		if ($ticket_info['organization_id'] && $this->verifyOrgId($ticket_info['organization_id'])) {
			$insert_ticket['organization_id'] = $ticket_info['organization_id'];
		}
		if ($ticket_info['assignee_id'] && $this->verifyUserId($ticket_info['assignee_id'])) {
			$insert_ticket['agent_id'] = $ticket_info['assignee_id'];
		}
		if ($ticket_info['group_id'] && $this->verifyTableId('agent_teams', $ticket_info['group_id'])) {
			$insert_ticket['agent_team_id'] = $ticket_info['group_id'];
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getBatch($page)
	{
		$res = $this->zd->sendGet('tickets', array('per_page' => self::PERPAGE, 'page' => $page));

		$batch = $res->get('tickets');

		return $batch;
	}
}
