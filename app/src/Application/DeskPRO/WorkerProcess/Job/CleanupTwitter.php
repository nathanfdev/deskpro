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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * This cleans up various temporary data
 */
class CleanupTwitter extends AbstractJob
{
	const DEFAULT_INTERVAL = 3600; // hourly

	public function run()
	{
		$db = App::getDb();
		$accounts = $db->fetchAll("
			SELECT *
			FROM twitter_accounts
		");

		$status_ids = array();

		foreach ($accounts AS $account) {
			$results = $db->fetchAllKeyValue("
				SELECT id, status_id
				FROM twitter_accounts_statuses
				WHERE account_id = ?
					AND status_type = 'timeline'
					AND is_favorited = 0
					AND (is_archived = 1 OR (agent_id IS NULL AND agent_team_id IS NULL))
				ORDER BY date_created DESC
				LIMIT 1000, 10000
			", array($account['id']));

			$db->deleteIn('twitter_accounts_statuses', array_keys($results));
			$status_ids = array_merge($status_ids, array_values($results));

			$searches = $db->fetchAll("
				SELECT *
				FROM twitter_accounts_searches
				WHERE account_id = ?
			", array($account['id']));

			foreach ($searches AS $search) {
				$results = $db->fetchAllCol("
					SELECT ss.account_status_id, accs.status_id
					FROM twitter_accounts_searches_statuses AS ss
					INNER JOIN twitter_accounts_statuses AS accs
					WHERE ss.search_id = ?
						AND accs.status_type = 'timeline'
						AND accs.is_favorited = 0
						AND (accs.is_archived = 1 OR (accs.agent_id IS NULL AND accs.agent_team_id IS NULL))
					ORDER BY ss.date_created DESC
					LIMIT 100, 10000
				", array($search['id']));
				if ($results) {
					$db->executeUpdate("
						DELETE FROM twitter_accounts_statuses
						WHERE status_type IS NULL AND id IN (" . implode(',', array_keys($results)) . ")
					");
					$status_ids = array_merge($status_ids, array_values($results));
				}
			}
		}

		if ($status_ids) {
			$i = 0;
			$total_deleted = 0;
			$status_ids_in = implode(',', $status_ids);
			do {
				$affected = $db->executeUpdate("
					DELETE FROM twitter_statuses
					WHERE id IN ($status_ids_in) AND in_reply_to_status_id IS NULL
				");
				if (!$affected) {
					break;
				}
				$total_deleted += $affected;

				$i++;
			} while($i < 5);

			$this->logStatus("Cleaned up $total_deleted statuses");
		}
	}
}
