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

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

class TicketWatchStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Ticket Watches';
	}

	public function countPages()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM tech_ticket_watch");
		if (!$count) {
			return 1;
		}

		return ceil($count / 500);
	}

	public function run($page = 1)
	{
		$start = ($page - 1) * 500;
		$batch = $this->getOldDb()->fetchAll("SELECT * FROM tech_ticket_watch ORDER BY id ASC LIMIT $start, 500");

		$this->getDb()->beginTransaction();
		try {
			foreach ($batch as $w) {
				$this->processWatch($w);
			}
			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}
	}

	/**
	 * @param array $watch_info
	 */
	protected function processWatch($watch_info)
	{
		$agent_id = $this->getMappedNewId('tech', $watch_info['techid']);
		if (!$agent_id) {
			return;
		}

		$ticket_id = $this->getMappedNewId('ticket', $watch_info['ticketid']);
		if (!$ticket_id) {
			return;
		}

		$insert_task = array();
		$insert_task['title']             = "Ticket Watch on {$ticket_id}";
		$insert_task['person_id']         = $agent_id;
		$insert_task['assigned_agent_id'] = $agent_id;
		$insert_task['date_created']      = date('Y-m-d H:i:s', $watch_info['timestamp_created']);
		if ($watch_info['completed']) {
			$insert_task['is_completed']   = 1;
			$insert_task['date_completed'] = date('Y-m-d H:i:s', $watch_info['timestamp_complete'] + 1);
		}

		$this->getDb()->insert('tasks', $insert_task);
	}
}
